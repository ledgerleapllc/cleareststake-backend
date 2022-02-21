<?php

namespace App\Http\Controllers;

use App\Exports\TransactionExport;
use App\Exports\TransactionUserExport;
use App\Exports\UsersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

use App\Http\Helper;

use App\User;
use App\Transaction;

use App\Mail\SubInvitation;
use App\TokenPrice;
use Laravel\Passport\Token;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as FacadesResponse;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

/**
 * Functions for admins users only
 */

class AdminController extends Controller
{
	/**
	 * Reset User Password
	 * @param int userId
	 * @param string password
	 * @return array
	 */
	public function resetUserPassword(Request $request) {
		$user = Auth::user();

		if ($user && $user->hasRole('admin')) {
			$userId = (int) $request->get('userId');
			$password = $request->get('password');

			if ($userId && $password) {
				$user = User::where('role', 'user')->where('id', $userId)->first();
				if (!$user) {
					return [
						'success' => false,
						'message' => 'Invalid user'
					];
				}

				$user->password = Hash::make($password);
				$user->save();

				return [
					'success' => true, 
					'message' => 'Password has been reset'
				];
			}
		}

		return ['success' => false];
	}

	/**
	 * Withdraw from an LP user's balance
	 * @param int userId
	 * @param int amount
	 * @return array
	 */
	public function withdraw(Request $request) {
		$user = Auth::user();

		if ($user && $user->hasRole('admin')) {
			$userId = (int) $request->get('userId');
			$amount = (int) $request->get('amount');

			if ($userId && $amount > 0) {
				$user = User::where('role', 'user')->where('id', $userId)->first();
				if ($user && (int) $user->balance >= $amount) {
					$user->balance = (int) $user->balance - $amount;
					if (!$user->withdraw_sum) $user->withdraw_sum = 0;
					$user->withdraw_sum = (int) $user->withdraw_sum + $amount;
					$user->save();

					$user->last_withdraw_date = $user->updated_at;
					$user->save();

					Helper::subtractBalance($amount, $user->in_fund);
					Helper::updateSetting('last_withdraw_date', Carbon::now());

					Helper::addTransaction([
				        'user_id' => $user->id,
				        'amount' => -$amount,
				        'action' => 'Withdraw Processed',
				        'balance' => $user->balance,
				    ]);
					return ['success' => true];
				}
			}
		}

		return ['success' => false];
	}

	/**
	 * Update Total Balance of all LPs
	 * @param int balance
	 * @return array
	 */
	public function updateBalance(Request $request) {
		$user = Auth::user();

		if ($user && $user->hasRole('admin')) {
			$balance = (int) $request->get('balance');

			if ($balance > 0) {
				$settings = Helper::getSettings();
				
				$current_balance = 0;
				if ($settings && isset($settings['total_balance']))
					$current_balance = (int) $settings['total_balance'];
				
				if ($current_balance <= 0) {
					return [
						'success' => false,
						'message' => 'The current balance is zero'
					];
				}

				$rate = (float) ($balance / $current_balance);

				Helper::updateBalance($balance);
				Helper::updateUsersBalance($rate);
				Helper::updateSetting('last_inflation_date', Carbon::now());

				return ['success' => true];
			}
		}

		return ['success' => false];
	}

	/**
	 * Get inflation values
	 * @return array
	 */
	public function getValues(Request $request) {
		$user = Auth::user();
		
		$last_staking_date = "";
		if ($user && $user->hasRole('admin')) {
			$transaction = Transaction::where('action', 'Inflation Deposit')
																->orderBy('created_at', 'desc')
																->first();
			if ($transaction) $last_staking_date = $transaction->created_at;
		}

		return [
			'success' => true,
			'last_staking_date' => $last_staking_date
		];
	}

	/**
	 * Get Single User
	 * @param int userId
	 * @return array
	 */
	public function getSingleUser($userId, Request $request) {
		$user = Auth::user();

		if ($user && $user->hasRole('admin')) {
			$user = User::where('role', 'user')->where('id', $userId)->first();
			if ($user) {
				$total = 0;
				$transactions = [];

				// Table Variables
				$sort_key = 'transactions.id';
				$sort_direction = 'desc';
				
				$data = $request->all();
				extract($data);

				$sort_key = trim($sort_key);
				$sort_direction = trim($sort_direction);
				
				$total = Transaction::has('user')
													->where('user_id', $user->id)
													->get()
													->count();
				$transactions = Transaction::has('user')
																	->where('user_id', $user->id)
																	->orderBy($sort_key, $sort_direction)
																	->get();
				$settings = Helper::getSettings();
				$tokenPrice = $settings['token_price'];
				foreach ($transactions as $trans) {
					if ($trans->action == 'Fund sale') {
						$trans['usd'] = $tokenPrice * (int) $trans->amount * (-1);
					} else {
						$trans['usd'] = '';
					}
				}

				$totalInFund = isset($settings['total_infund']) ? (int) $settings['total_infund'] : 0;

				return [
					'success' => true,
					'total' => $total,
					'transactions' => $transactions,
					'user' => $user,
					'fund_total' => $totalInFund,
					'usd_fund_total' => $tokenPrice * (int) $totalInFund
				];
			}
		}

		return ['success' => false];
	}

	/**
	 * Get All Users
	 * @return array
	 */
	public function getAllUsers(Request $request) {
		$user = Auth::user();
		$users = [];

		if ($user && $user->hasRole('admin')) {
			$users = User::where('role', 'user')->orderBy('first_name', 'asc')->get();
		}

		return [
			'success' => true,
			'users' => $users
		];
	}

	/**
	 * Get User List
	 * @param bool in_fund
	 * @return array
	 */
	public function getUsers(Request $request) {
		$user = Auth::user();

		$users = [];
		$total = 0;
		
		// Table Variables
		$page_length = 10;
		$sort_key = 'users.first_name';
		$sort_direction = 'asc';
		$page_id = 1;
		
		$data = $request->all();
		extract($data);

		$page_id = (int) $page_id;
		$page_length = (int) $page_length;
		$sort_key = trim($sort_key);
		$sort_direction = trim($sort_direction);
		$inFund = $request->in_fund ?? null;
		
		if ($page_id < 1) $page_id = 1;
		$start = ($page_id - 1) * $page_length;

		$totalBalance = 0;

		// Role Check
		if ($user && $user->hasRole('admin')) { // Admin
			$total = User::where('id', '>', 0)
							->where('role', '!=', 'admin')
							->where(function ($query) use ($inFund) {
								if (!is_null($inFund)) {
									$query->where('in_fund', '=', $inFund);
								}
							})
							->get()
							->count();
			$users = User::where('id', '>', 0)
							->where('role', '!=', 'admin')
							->where(function ($query) use ($inFund) {
								if (!is_null($inFund)) {
									$query->where('in_fund', '=', $inFund);
								}
							})
							->orderBy($sort_key, $sort_direction)
							->offset($start)
							->limit($page_length)
							->get();
			foreach ($users as $user) {
				$totalInflation = Transaction::where('action', 'Inflation Deposit')
					->where('user_id', $user->id)->get()->sum('amount');
				$user['total_inflation'] = $totalInflation;
			}

			$totalBalance = DB::table('users')
				->where('id', '>', 0)
				->where('role', '!=', 'admin')
				->where(function ($query) use ($inFund) {
					if (!is_null($inFund)) {
						$query->where('in_fund', '=', $inFund);
					}
				})
				->sum('balance');
		}

		return [
			'success' => true,
			'total' => $total,
			'users' => $users,
			'total_balance' => $totalBalance
		];
	}

	/**
	 * Deposit to LP user's balance
	 * @param int userId
	 * @param int amount
	 * @return array
	 */
	public function deposit(Request $request) {
		$user = Auth::user();

		if ($user && $user->hasRole('admin')) {
			$userId = (int) $request->get('userId');
			$amount = (int) $request->get('amount');

			if ($userId && $amount > 0) {
				$user = User::where('role', 'user')->where('id', $userId)->first();
				if ($user) {
					$user->balance = (int) $user->balance + $amount;
					$user->save();

					Helper::addBalance($amount, $user->in_fund);

					Helper::addTransaction([
						'user_id' => $user->id,
						'amount' => $amount,
						'action' => 'Deposit',
						'balance' => $user->balance,
		      		]);
					return ['success' => true];
				}
			}
		}

		return ['success' => false];
	}

	/**
	 * Update a fund user's status
	 * @param int userId
	 * @param bool in_fund
	 * @return array
	 */
	public function updateInFundUser($userId, Request $request) {
		$user = Auth::user();

		if ($user && $user->hasRole('admin')) {
			$user = User::where('role', 'user')->where('id', $userId)->first();
			if ($user) {
				// Validator
				$validator = Validator::make($request->all(), [
					'in_fund' => 'required|boolean'
					]);

				if ($validator->fails()) {
					return [
						'success' => false
					];
				}

				if ($user->in_fund != $request->in_fund) {
					$settings = Helper::getSettings();
					$totalInFund = isset($settings['total_infund']) ? (int) $settings['total_infund'] : 0;

					if ($request->in_fund == 1) {
						$totalInFund += (int) $user->balance;
					} else {
						$totalInFund -= (int) $user->balance;
					}
					Helper::updateSetting('total_infund', $totalInFund);

					$user->in_fund = $request->in_fund;
					$user->save();
				}

				return [
					'success' => true
				];
			}
		}

		return ['success' => false];
	}

	/**
	 * Download CSV containing LP user data/status/history
	 * @param bool in_fund
	 * @return array
	 */
	public function downloadUserCSV2(Request $request) {
		$filename = 'export_users_' . date('Y-m-d') . '_' . date('H:i:s') . '.csv';
	
		$settings = Helper::getSettings();
		$total_balance = 0;
		if ($settings && isset($settings['real_total_balance'])) {
			$total_balance = (int) $settings['real_total_balance'];
		}
		$totalInFund = isset($settings['real_total_infund']) ? (int) $settings['real_total_infund'] : 0;

		$sort_key = 'users.first_name';
		$sort_direction = 'asc';
		$data = $request->all();
		extract($data);
		$sort_key = trim($sort_key);
		$sort_direction = trim($sort_direction);
		$inFund = $request->in_fund ?? null;
		$users = User::where('id', '>', 0)
			->where('role', '!=', 'admin')
			->where(function ($query) use ($inFund) {
				if (!is_null($inFund)) {
					$query->where('in_fund', '=', $inFund);
				}
			})
			->orderBy($sort_key, $sort_direction)
			->get();
			$myArray = [];

			if ($inFund == 1) {
				array_push($myArray,	[
					'Name',
					'Fund',
					'Total Tokens',
					'% of Fund Total',
					'Withdraw Sum',
					'Last Withdraw',
					'Total inflation'
					]);
			} else {
				array_push($myArray,	[
					'Name',
					'Fund',
					'Total Tokens',
					'% of Total',
					'Withdraw Sum',
					'Last Withdraw',
					'Total inflation'
					]);
			}

			foreach ($users as $user) {
				$totalInflation = Transaction::where('action', 'Inflation Deposit')
					->where('user_id', $user->id)->get()->sum('amount');
				$percent = $inFund == 1 ? (($user->balance / $totalInFund) * 100) : (($user->balance / $total_balance) * 100);
				array_push($myArray, [
					$user->first_name.' '.$user->last_name,
					(int) $user->in_fund ? "Yes" : "No",
					$user->balance,
					$percent,
					$user->withdraw_sum,
					$user->last_withdraw_date,
					$totalInflation 
				]);
			}
			return FacadesExcel::download(new UsersExport($myArray), $filename);
	}

	/**
	 * Download CSV containing transactions data
	 * @param bool in_fund
	 * @return array
	 */
	public function downloadTransactionCSV2(Request $request) {
		$filename = 'export_transactions_' . date('Y-m-d') . '_' . date('H:i:s') . '.csv'; 
		$sort_key = 'transactions.id';
		$sort_direction = 'desc';
		$data = $request->all();
		extract($data);
		$sort_key = trim($sort_key);
		$sort_direction = trim($sort_direction);
		$inFund = $request->in_fund ?? null;

		$transactions = Transaction::with('user')
			->join('users', 'transactions.user_id', '=', 'users.id')
			->where(function ($query) use ($inFund) {
				if (!is_null($inFund)) {
					$query->where('users.in_fund', '=', $inFund);
				}
			})->select(['transactions.*'])
			->has('user')
			->orderBy($sort_key, $sort_direction)
			->get();
		$myArray = [];
		$settings = Helper::getSettings();
		$tokenPrice = $settings['token_price'];
		foreach ($transactions as $transaction) {
			array_push($myArray, [
				$transaction->created_at,
				(int) $transaction->user->in_fund ? "Yes" : "No",
				$transaction->action,
				$transaction->amount,
				$transaction->action == 'Fund sale' ? $tokenPrice * (int) $transaction->amount * (-1) : '',
				$transaction->user->first_name.' '.$transaction->user->last_name
			]);
		}
		return FacadesExcel::download(new TransactionExport($myArray), $filename);
	}

	/**
	 * Fund sale selloff of multiple fund users
	 * @param array fund_sale_list
	 * @return array
	 */
	public function fundSale(Request $request) {
		$user = Auth::user();

		if ($user && $user->hasRole('admin')) {
			$fundSaleList = $request->fund_sale_list;
			// dd($fundSaleList);

			foreach ($fundSaleList as $fundSale) {
				$amount = (int) $fundSale['amount'];
				$userId = $fundSale['user_id'];
				if ($userId && $amount > 0) {
					$user = User::where('role', 'user')->where('id', $userId)
						->where('in_fund', 1)->first();
					if ($user && (int) $user->balance >= $amount) {
						$user->balance = (int) $user->balance - $amount;
						$user->save();

						Helper::subtractBalance($amount, $user->in_fund);
						Helper::addTransaction([
							'user_id' => $user->id,
							'amount' => -$amount,
							'action' => 'Fund sale',
							'balance' => $user->balance,
						]);
					}
				}
			}
			return ['success' => true,
					'transaction_date' => Carbon::now()];
		}
		return ['success' => false];
	}

	/**
	 * Download CSV containing user transactions data
	 * @param int userId
	 * @return FacadesExcel
	 */
	public function downloadUserTransctionCsv($userId, Request $request) {
		$filename = 'export_user_transactions_' . date('Y-m-d') . '_' . date('H:i:s') . '.csv'; 
		// Table Variables
		$sort_key = 'transactions.id';
		$sort_direction = 'desc';
		
		$data = $request->all();
		extract($data);

		$sort_key = trim($sort_key);
		$sort_direction = trim($sort_direction);
		
		$transactions = Transaction::has('user')
											->where('user_id', $userId)
											->orderBy($sort_key, $sort_direction)
											->get();
		$settings = Helper::getSettings();
		$tokenPrice = $settings['token_price'];
		$myArray = [];
		foreach ($transactions as $trans) {
			$usd;
			if ($trans->action == 'Fund sale') {
				$usd = $trans['usd'] = $tokenPrice * (int) $trans->amount * (-1);
			} else {
				$usd = $trans['usd'] = '';
			}

			array_push($myArray, [
				$trans->created_at,
				$trans->action,
				$trans->amount,
				$usd,
				$trans->balance
			]);
		}
		
		return FacadesExcel::download(new TransactionUserExport($myArray), $filename);
	}

	/**
	 * Get the price of CSPR/USD
	 * @return array
	 */
	public function getCSPRPrice(Request $request) {
		$page_id = 1;
		$page_length = 10;
		
		$data = $request->all();
		extract($data);

		$page_id = (int) $page_id;
		$page_length = (int) $page_length;

		if ($page_id < 1) $page_id = 1;
		$start = ($page_id - 1) * $page_length;
		$startDate = date('Y-m-d', strtotime('2021-06-01'));
		$result = TokenPrice::select(DB::raw('year(created_at) as year, month(created_at) as month, AVG(price) AS average_price, MIN(price) AS lowest_price , MAX(price) AS highest_price'))
			->where('created_at', '>=', $startDate)
			->groupBy('year')
        	->groupBy('month')
			->orderBy('year', 'asc')
			->orderBy('month', 'asc')
			->offset($start)
			->limit($page_length)
			->get();

		$total = TokenPrice::select(DB::raw('year(created_at) as year, month(created_at) as month, AVG(price) AS average_price, MIN(price) AS lowest_price , MAX(price) AS highest_price'))
			->where('created_at', '>=', $startDate)
			->groupBy('year')
        	->groupBy('month')
			->orderBy('year', 'asc')
			->orderBy('month', 'asc')
			->get()
			->count();

		return [
			'success' => true,
			'data' => $result,
			'total' => $total
		];
	}
}
