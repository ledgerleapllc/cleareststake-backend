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

use App\Http\Helper;

use App\User;
use App\TokenPrice;

use App\Mail\RequestWithdraw;

use Carbon\Carbon;

/**
 * User specific functions
 */
class UserController extends Controller
{
	/**
	 * Get GraphInfo
	 * @return array
	 */
	public function getGraphInfo(Request $request) {
		$startDate = $request->get('startDate');
		$endDate = $request->get('endDate');

		if ($startDate) {
			$startDate .= ' 00:00:00';
		}
		if ($endDate) {
			$endDate .= ' 23:59:59';
		}

		$user = Auth::user();
		$graphData = [];

		if ($user && $user->hasRole('user')) {
			$items = TokenPrice::where(function ($query) use ($startDate, $endDate) {
									if ($startDate) {
										$query->where('created_at', '>=', $startDate);
									}
									if ($endDate) {
										$query->where('created_at', '<=', $endDate);
									}
								})
								->orderBy('created_at', 'desc')
								->limit(168)
								->get();

			if ($items && count($items)) {
				$items = array_reverse($items);
				foreach ($items as $item) {
					$name = Carbon::parse($item->created_at)->format("Y-m-d H:i");
					$graphData[] = [
						'name' => $name,
						'Price' => $item->price
					];
				}
			}
		}

		return [
			'success' => true,
			'graphData' => $graphData,
		];
	}

	/**
	 * Self Withdraw
	 * @param int amount
	 * @return array
	 */
	public function withdraw(Request $request) {
		$user = Auth::user();

		if ($user && $user->hasRole('user')) {
			$amount = (int) $request->get('amount');
			if ($amount > 0 && (int) $user->balance >= $amount) {
				// ENV Check
				$envMailer = env('MAIL_MAILER');
				$envHost = env('MAIL_HOST');
				$envPort = env('MAIL_PORT');
				$envUsername = env('MAIL_USERNAME');
				$envPassword = env('MAIL_PASSWORD');
				$envEncryption = env('MAIL_ENCRYPTION');
				$envAddress = env('MAIL_FROM_ADDRESS');
				$envName = env('MAIL_FROM_NAME');
				if (!$envMailer || !$envHost || !$envPort || !$envUsername || !$envPassword || !$envEncryption || !$envAddress || !$envName) {
					return [
						'success' => false,
						'message' => 'We cannot send email, please try again later'
					];
				}

				Mail::to(env('ADMIN_EMAIL'))->send(new RequestWithdraw($user->first_name . ' ' . $user->last_name, $amount));
				
				$user->last_withdraw_request = Carbon::now();
				$user->save();

				return ['success' => true];
			}
		}

		return ['success' => false];
	}
}
