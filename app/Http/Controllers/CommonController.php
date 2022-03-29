<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

use App\Setting;
use App\User;
use App\Transaction;
use App\Log;

use App\Http\Helper;

use App\Mail\ResetPasswordLink;
use App\Mail\HelpRequest;

use Laravel\Passport\Token;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * Common Functions for both admins and LP users
 */
class CommonController extends Controller
{
	/**
	 * Send Help Request
	 * @param string text
	 * @return array
	 */
	public function sendHelpRequest(Request $request) {
		$user = Auth::user();

		if ($user) {
			$text = $request->get('text');
			if (!$text) {
				return [
					'success' => false,
					'message' => 'Please input your question or request'
				];
			}

			// ENV Check
			$envMailer = env('MAIL_MAILER');
			$envHost = env('MAIL_HOST');
			$envPort = env('MAIL_PORT');
			$envUsername = env('MAIL_USERNAME');
			$envPassword = env('MAIL_PASSWORD');
			$envEncryption = env('MAIL_ENCRYPTION');
			$envAddress = env('MAIL_FROM_ADDRESS');
			$envName = env('MAIL_FROM_NAME');
			if (
				!$envMailer ||
				!$envHost ||
				!$envPort ||
				!$envUsername ||
				!$envPassword ||
				!$envEncryption ||
				!$envAddress ||
				!$envName
			) {
				return [
					'success' => false,
					'message' => 'We cannot send email, please try again later'
				];
			}

			Mail::to(env('ADMIN_EMAIL'))->send(new HelpRequest($user->email, $text));
			return ['success' => true];
		}

		return ['success' => false];
	}

	/**
	 * Change Email
	 * @param string email
	 * @return array
	 */
	public function changeEmail(Request $request) {
		$user = Auth::user();

		if ($user) {
			$email = $request->get('email');

			if ($email) {
				$temp = User::where('email', $email)
										->where('id', '!=', $user->id)
										->first();

				if ($temp) {
					return [
						'success' => false,
						'message' => 'This email is already in use.'
					];
				}

				$user->email = $email;
				$user->save();

				return ['success' => true];
			}
		}

		return ['success' => false];
	}

	/**
	 * Change Password
	 * @param string password
	 * @param string new_password
	 * @return array
	 */
	public function changePassword(Request $request) {
		$user = Auth::user();

		$password = $request->get('password');
		$new_password = $request->get('new_password');

		if ($password && $new_password && $user) {
			if (!Hash::check($password, $user->password)) {
				return [
					'success' => false,
					'message' => 'Current password is wrong'
				];
			}

			$user->password = Hash::make($new_password);
			$user->save();

			return ['success' => true];
		}

		return ['success' => false];
	}

	/**
	 * Reset Password
	 * @param string email
	 * @param string password
	 * @param string token
	 * @return array
	 */
	public function resetPassword(Request $request) {
		// Validator
		$validator = Validator::make($request->all(), [
			'email' => 'required|email',
			'password' => 'required',
			'token' => 'required'
		]);

		if ($validator->fails()) return ['success' => false];
		
		$email = $request->get('email');
		$password = $request->get('password');
		$token = $request->get('token');

		// Token Check
		$temp = DB::table('password_resets')
			->where('email', $email)
			->first();
		if (!$temp) return ['success' => false];
		if (!Hash::check($token, $temp->token)) return ['success' => false];

		// User Check
		$user = User::where('email', $email)->first();

		if (!$user) {
			return [
				'success' => false,
				'message' => 'Invalid user'
			];
		}

		$user->password = Hash::make($password);
		$user->save();

		// Clear Tokens
		DB::table('password_resets')
			->where('email', $email)
			->delete();

		return ['success' => true];
	}
	
	/**
	 * Send Reset Email to LP user
	 * @param string email
	 * @return array
	 */
	public function sendResetEmail(Request $request) {
		// Validator
		$validator = Validator::make($request->all(), [
			'email' => 'required|email',
		]);

		if ($validator->fails()) return ['success' => false];

		$email = $request->get('email');
		$user = User::where('email', $email)->first();

		if (!$user) {
			return [
				'success' => false,
				'message' => 'Email is not valid'
			];
		}

		// Clear Tokens
		DB::table('password_resets')
			->where('email', $email)
			->delete();

		// Generate New One
		$token = Str::random(60);
		DB::table('password_resets')->insert([
			'email' => $email,
			'token' => Hash::make($token),
			'created_at' => Carbon::now()
		]);

		// ENV Check
		$envMailer = env('MAIL_MAILER');
		$envHost = env('MAIL_HOST');
		$envPort = env('MAIL_PORT');
		$envUsername = env('MAIL_USERNAME');
		$envPassword = env('MAIL_PASSWORD');
		$envEncryption = env('MAIL_ENCRYPTION');
		$envAddress = env('MAIL_FROM_ADDRESS');
		$envName = env('MAIL_FROM_NAME');
		if (
			!$envMailer ||
			!$envHost ||
			!$envPort ||
			!$envUsername ||
			!$envPassword ||
			!$envEncryption ||
			!$envAddress ||
			!$envName
		) {
			return [
				'success' => false,
				'message' => 'We cannot send email, please try again later'
			];
		}

		$resetUrl = $request->header('origin') . '/password/reset/' . $token . '?email=' . urlencode($email);
		Mail::to($user)->send(new ResetPasswordLink($resetUrl));

		return ['success' => true];
	}

	/**
	 * Get admin settings
	 * @return array
	 */
	public function getSettings(Request $request) {
		$settings = Helper::getSettings();
		
		return [
			'success' => true,
			'settings' => $settings
		];
	}

	/**
	 * Get logs by user
	 * @param int userId
	 * @return array
	 */
	public function getLogs(Request $request) {
		$logs = [];

		// Table Variables
		$userId = (int) $request->get('userId');
		$page_id = 1;
		$page_length = 10;
		$sort_key = 'log.id';
		$sort_direction = 'desc';
		
		$data = $request->all();
		extract($data);

		$page_id = (int) $page_id;
		$page_length = (int) $page_length;
		$sort_key = trim($sort_key);
		$sort_direction = trim($sort_direction);

		if ($page_id < 1) $page_id = 1;
		$start = ($page_id - 1) * $page_length;

		$total = Log::where('user_id', $userId)
								->get()
								->count();
		$logs = Log::where('user_id', $userId)
								->orderBy($sort_key, $sort_direction)
								->offset($start)
								->limit($page_length)
								->get();
		
		return [
			'success' => true,
			'logs' => $logs,
			'total' => $total
		];
	}

	/**
	 * Get Transaction List
	 * @param bool in_fund
	 * @return array
	 */
	public function getTransactions(Request $request) {
		$user = Auth::user();

		$transactions = [];
		$total = 0;

		// Table Variables
		$page_length = 10;
		$page_id = 1;
		$sort_key = 'transactions.id';
		$sort_direction = 'desc';
		
		$data = $request->all();
		extract($data);

		$page_id = (int) $page_id;
		$page_length = (int) $page_length;
		$sort_key = trim($sort_key);
		$sort_direction = trim($sort_direction);
		$inFund = $request->in_fund ?? null;
		if ($page_id < 1) $page_id = 1;
		$start = ($page_id - 1) * $page_length;

		// Role Check
		if ($user && $user->hasRole('admin')) {
			$total = Transaction::has('user')
								->get()
								->count();
			$transactions = Transaction::with('user')->join('users', 'transactions.user_id', '=', 'users.id')
										->where(function ($query) use ($inFund) {
											if (!is_null($inFund)) {
												$query->where('users.in_fund', '=', $inFund);
											}
										})->select(['transactions.*'])
										->has('user')
										->orderBy($sort_key, $sort_direction)
										->offset($start)
										->limit($page_length)
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
		} else {
			$total = Transaction::has('user')
								->where('user_id', $user->id)
								->get()
								->count();
			$transactions = Transaction::has('user')
										->where('user_id', $user->id)
										->orderBy($sort_key, $sort_direction)
										->offset($start)
										->limit($page_length)
										->get();
		}

		return [
			'success' => true,
			'transactions' => $transactions,
			'total' => $total
		];
	}
}
