<?php
namespace App\Traits;

use App\Carrier;
use App\Carrierservice;
use App\Country;
use App\Department;
use App\FileType;
use App\HoldServiceTicket;
use App\InstalerRate;
use App\Notessku;
use App\OrderCreateShipment;
use App\OrderInternalNote;
use App\OrderItemWorking;
use App\Orders;
use App\OrdersItems;
use App\Rate;
use App\ServiceRequest;
use App\Tags;
use App\User;
use App\UserPermission;
use App\Warehouse;
use App\WorkFlow;use Auth;use Carbon\Carbon;use DB;

trait Common {
	/**
	 * Get API Auth Code for ship Station
	 * using Key, Secret
	 * encoding use base64
	 */
	function shipstationAuth($accountType = false) {

		if ($accountType == "FLOCK") {
			$AuthKey = env("FLOCK_SS_AUTH_KEY");
			$AuthSecret = env("FLOCK_SS_AUTH_SECRET");
		} else {
			$AuthKey = env("SS_AUTH_KEY");
			$AuthSecret = env("SS_AUTH_SECRET");
		}

		$encodeString = base64_encode($AuthKey . ':' . $AuthSecret);

		$code = "Basic " . $encodeString;

		return $code;
	}

	/**
	 * using for get data from ship station using curl
	 * params : endpoint of shipstation
	 * response_type : json
	 */
	function ss_get_curl($endPoint) {

		$hostname = env("SS_HOST_URL");

		$apiUrl = env("SS_API_URL");
		try {
			$AuthCode = $this->shipstationAuth();

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $apiUrl . $endPoint);
			curl_setopt($ch, CURLOPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 300);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"Host:" . $hostname,
				"Authorization:" . $AuthCode,
			]);

			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//\App\Helpers\Helper::createMonitor("SHIPSTAION DEFAULT", "Ok");
			curl_close($ch);
		} catch (\Exception $e) {
			$message = $e->getMessage();
			//\App\Helpers\Helper::createMonitor("SHIPSTAION DEFAULT", "Failed", $message);
		} catch (\Throwable $th) {
			$message = $th->getMessage();
			//\App\Helpers\Helper::createMonitor("SHIPSTAION DEFAULT", "Failed", $message);
		}
		return $response;
	}

	/**
	 * using for get data from ship station using curl
	 * params : endpoint of shipstation
	 * response_type : json
	 */
	function ss_get_hook_curl($endPoint, $accountType = false) {
		\Log::channel('api_logs')->info('SS GET HOOK CURL STARTS');
		try {
			$AuthCode = $this->shipstationAuth($accountType);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $endPoint);
			curl_setopt($ch, CURLOPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 300);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"Authorization:" . $AuthCode,
			]);
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//\App\Helpers\Helper::createMonitor("SHIPSTAION " . $accountType, "Ok");
			curl_close($ch);
		} catch (\Exception $e) {
			$message = $e->getMessage();
			//\App\Helpers\Helper::createMonitor("SHIPSTAION " . $accountType, "Failed", $message);
		} catch (\Throwable $th) {
			$message = $th->getMessage();
			//\App\Helpers\Helper::createMonitor("SHIPSTAION " . $accountType, "Failed", $message);
		}

		\Log::channel('api_logs')->info('SS GET HOOK CURL ENDS');
		return $response;
	}

	/**
	 * using for get data from ship station using curl
	 * params : endpoint of shipstation
	 * params : postData JSON
	 * response_type : JSON
	 */
	function ss_post_curl($endPoint, $postData) {
		$hostname = env("SS_HOST_URL");

		$apiUrl = env("SS_API_URL");

		try {
			$AuthCode = $this->shipstationAuth();

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $apiUrl . $endPoint);
			curl_setopt($ch, CURLOPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 300);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"Host:" . $hostname,
				"Authorization:" . $AuthCode,
				"Content-Type: application/json",
			]);
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//\App\Helpers\Helper::createMonitor("SHIPSTAION DEFAULT", "Ok");
			curl_close($ch);
		} catch (\Exception $e) {
			$message = $e->getMessage();
			//\App\Helpers\Helper::createMonitor("SHIPSTAION Default", "Failed", $message);
		} catch (\Throwable $th) {
			$message = $th->getMessage();
			//\App\Helpers\Helper::createMonitor("SHIPSTAION Default", "Failed", $message);
		}

		\Log::channel('api_logs')->info('SS POST CURL ENDS');
		return $response;
	}

	function postViaCurl($url, $headers, $postData, $method = "POST") {
		usleep(500 * 1000);
		// sleep(10);
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 300);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$response = curl_exec($ch);

			//\Log::channel('zapier_sos')->info('Curl response.' . json_encode($response));

			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			//\App\Helpers\Helper::createMonitor("CURL Function ", "Ok");

		} catch (\Exception $e) {
			$message = $e->getMessage();
			//\App\Helpers\Helper::createMonitor("CURL Function", "Failed", $message);
			//\Log::channel('zapier_sos')->info('Curl response.' . json_encode($response));
		} catch (\Throwable $th) {
			$message = $th->getMessage();
			//\App\Helpers\Helper::createMonitor("CURL Function", "Failed", $message);
			//\Log::channel('zapier_sos')->info('Curl response.' . json_encode($response));
		}

		\Log::channel('api_logs')->info('POST VIA CURL FUNCTION ENDS');
		return $response;

	}

	function postViaCurlForAi($url, $headers, $postData, $method = "POST") {
		try {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_ENCODING, "");
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 300);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			//\App\Helpers\Helper::createMonitor("CAMERA API ", "Ok");
			curl_close($ch);
		} catch (\Exception $e) {
			$message = $e->getMessage();
			//\App\Helpers\Helper::createMonitor("CAMERA API", "Failed", $message);
		} catch (\Throwable $th) {
			$message = $th->getMessage();
			//\App\Helpers\Helper::createMonitor("CAMERA API", "Failed", $message);
		}

		return response()->json(['response' => $response, 'status' => $httpCode, 'error' => $error]);

	}

	/**
	 * Convert db date to US standard date
	 * mm/dd/yyyy
	 */
	function showdate($date = false, $format = "m/d/Y") {
		if (isset($date) && !empty($date)) {
			return date($format, strtotime($date));
		}
	}

	/**
	 * Convert db date to US standard date
	 * mm/dd/yyyy H:i:s
	 */
	function showdateTime($date = false) {
		if (isset($date) && !empty($date)) {
			return date('m/d/Y H:i:s', strtotime($date));
		}
	}

	function html_cut($text, $max_length) {
		$tags = array();
		$result = "";

		$is_open = false;
		$grab_open = false;
		$is_close = false;
		$in_double_quotes = false;
		$in_single_quotes = false;
		$tag = "";

		$i = 0;
		$stripped = 0;

		$stripped_text = strip_tags($text);

		while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length) {
			$symbol = $text[$i];
			$result .= $symbol;

			switch ($symbol) {
			case '<':
				$is_open = true;
				$grab_open = true;
				break;

			case '"':
				if ($in_double_quotes) {
					$in_double_quotes = false;
				} else {
					$in_double_quotes = true;
				}

				break;

			case "'":
				if ($in_single_quotes) {
					$in_single_quotes = false;
				} else {
					$in_single_quotes = true;
				}

				break;

			case '/':
				if ($is_open && !$in_double_quotes && !$in_single_quotes) {
					$is_close = true;
					$is_open = false;
					$grab_open = false;
				}

				break;

			case ' ':
				if ($is_open) {
					$grab_open = false;
				} else {
					$stripped++;
				}

				break;

			case '>':
				if ($is_open) {
					$is_open = false;
					$grab_open = false;
					array_push($tags, $tag);
					$tag = "";
				} else if ($is_close) {
					$is_close = false;
					array_pop($tags);
					$tag = "";
				}

				break;

			default:
				if ($grab_open || $is_close) {
					$tag .= $symbol;
				}

				if (!$is_open && !$is_close) {
					$stripped++;
				}

			}

			$i++;
		}

		while ($tags) {
			$result .= "</" . array_pop($tags) . ">";
		}

		return $result;
	}

	function getDepartment($where, $install = false) {
		if ($install) {
			return Department::where($where)->orWhere([$install])->orderBy('name', 'ASC')->pluck('name', 'id')->all();
		} else {
			return Department::where($where)->orderBy('name', 'ASC')->pluck('name', 'id')->all();
		}
	}

	function getItemWorking($itemId, $workflowId) {

		return OrderItemWorking::select('department_id', 'level')->with([
			'department',
		])->where(['order_item_id' => $itemId, 'workflow_id' => $workflowId, 'status' => 'ACTIVE'])->whereIn('stage', ['COMPLETE', 'INPROGRESS'])->get();

	}

	function getOpreator($where) {
		return User::role('Operator')->where($where)->orderBy('name', 'ASC')->pluck('name', 'users.id')->all();
	}

	function getWorkFlow($where) {
		return WorkFlow::where($where)->orderBy('order_by', 'ASC')->pluck('name', 'id')->all();
	}

	function loggedInUserId() {
		if (Auth::check()) {
			return Auth::user()->id;
		} else {
			return 1;
		}
	}

	function workFlowSqlQuery() {
		$sql = "SELECT wf.id wf_id, wf.name wf_name, wfd.id wfd_id, wfd.level wfd_level, wfd.deleted_at, d.name dep_name, d.type dep_type, d.id dep_id FROM workflows wf
		JOIN workflow_departments wfd ON wfd.workflow_id = wf.id AND wfd.status = 'ACTIVE' AND wf.status = 'ACTIVE' AND wf.id = ?
        JOIN departments d ON d.id = wfd.department_id AND d.status = 'ACTIVE' ORDER BY wfd.level";
		//dd($sql);
		return $sql;
	}

	function workFlowDepartmentStatusSqlQuery() {
		$sql = "SELECT ordiw.id, ordiw.order_item_id, ordiw.department_id dep_id, ordiw.start, ordiw.end, ordiw.stage, ordiw.level FROM order_item_workings ordiw
        WHERE ordiw.workflow_id = ? AND ordiw.status = 'ACTIVE'";
		return $sql;
	}

	function getTicketFooterDepartment($orderDeatilId, $departmentId, $type) {

		$return = array();

		$sql = "SELECT d.id, d.name FROM order_item_workings oiw JOIN departments d ON d.id = oiw.department_id AND d.status = 'ACTIVE' AND oiw.status = 'ACTIVE' AND oiw.stage = 'COMPLETE' AND oiw.order_item_id = ? ORDER BY oiw.end DESC";

		$pervoiusWorking = DB::select($sql, [$orderDeatilId]);

		$departments = array();

		if (isset($pervoiusWorking) && !empty($pervoiusWorking)) {
			$return[0] = $pervoiusWorking[0]->name;
			if (strlen($return[0]) > 15) {
				$return[0] = substr($return[0], 0, 15) . '....';
			}
			foreach ($pervoiusWorking as $key => $value) {
				$departments[] = $value->id;
			}
		} else {
			if ($type == "TICKET") {
				$return[0] = 'New Ticket';
			} else {
				$return[0] = 'New Order';
			}
		}

		$sql1 = "SELECT d.id, d.name, d.type FROM departments d  WHERE d.id = ?";

		$currentWorking = DB::select($sql1, [$departmentId]);
		$return[1] = "<u>" . $currentWorking[0]->name . "</u>";

		$departments[] = $departmentId;

		$sql2 = "SELECT d.id, d.name FROM orders_items oi JOIN workflow_departments wd ON wd.workflow_id = oi.assign_workflow AND wd.status = 'ACTIVE' JOIN departments d ON d.id = wd.department_id WHERE d.status = 'ACTIVE'";

		if (isset($departments) && !empty($departments)) {
			$depId = "'" . implode("', '", $departments) . "'";
			$sql2 .= " AND d.id NOT IN ($depId) AND oi.id = ?";
		}

		$sql2 .= " LIMIT 1";

		$nextWorking = DB::select($sql2, [$orderDeatilId]);
		if (isset($nextWorking) && !empty($nextWorking)) {
			$return[2] = $nextWorking[0]->name;
		} else {
			if ($type == 'ORDER') {
				$return[2] = 'Order Complete';
			} elseif ($type == 'TICKET') {
				$return[2] = 'INSTALLED';
			} else {
				$return[2] = $this->finalDepart($orderDeatilId);
			}
		}
		if (strlen($return[2]) > 15) {
			$return[2] = substr($return[2], 0, 15) . '....';
		}

		return implode(" > ", $return);
	}

	private function finalDepart($id) {
		$order_id = OrdersItems::find($id);
		$final_dep_id = Orders::find($order_id->orders_id);

		if (!empty($final_dep_id)) {
			$final = Department::find($final_dep_id->final_department);
			return isset($final->name) ? $final->name : "Order Complete";
		}
	}

	public function getWeekDay($weekNo, $dayNo) {
		return date('m/d/Y', strtotime("monday -$weekNo week"));
	}

	public function getApproverUsers() {
		$users = UserPermission::with([
			'users',
		])->select([
			'user_id',
		])->where([
			'permission_name' => 'Document Approver',
			'status' => 'ACTIVE',
		])->get();

		if (isset($users) && !empty($users)) {
			$approvers = [];
			foreach ($users as $key => $value) {
				$approvers[$value->users->id] = $value->users->name;
			}
			return $approvers;
		} else {
			return false;
		}
	}

	public function getFileType($where) {
		$fileTypes = FileType::where($where)->select('id', 'file_type')->orderBy('file_type', 'ASC')->pluck('file_type', 'id')->all();
		return $fileTypes;
	}

	public function getAllTags($where) {
		$fileTypes = Tags::where($where)->select('id', 'name')->orderBy('name', 'ASC')->pluck('name', 'id')->all();
		return $fileTypes;
	}

	public function getCarriersList() {
		$carriersList = Carrier::select("code", 'name', "primary")->orderBy('name', 'ASC')->get();
		return $carriersList;
	}

	public function getCarriers($where, $whereIn) {

		if (isset($whereIn) && !empty($whereIn)) {

			$carriers = Carrierservice::where($where)->whereIn('carrierCode', $whereIn)->select("code", 'name', 'carrierCode')->orderBy('name', 'ASC')->pluck('name', 'code', 'carrierCode')->all();
		} else {
			$carriers = Carrierservice::where($where)->select("code", 'name', 'carrierCode')->orderBy('name', 'ASC')->pluck('name', 'code', 'carrierCode')->all();
		}

		return $carriers;
	}

	public function getShipAccount($carrierCode) {
		$getService = Carrierservice::with('carrier')->whereIn('code', $carrierCode)->get();
		//$carriers = Carrier::whereIn('code', $carrierCode)->get();
		return $getService;
	}

	public function getAllWarehouse($where) {
		$warehouse = Warehouse::where($where)->select("id", 'warehouseName')->orderBy('warehouseName', 'ASC')->pluck('warehouseName', 'id')->all();
		return $warehouse;
	}

	public function geoCodeOrders() {

		$ordersData = Orders::select(['id', 'sstreet1', 'sstreet2', 'sstreet3', 'scity', 'sstate', 'spostalCode', 'scountry'])->whereRaw('`lat` IS NULL AND `long` IS NULL')->whereNotIn('status', ['COMPLETED', 'CANCELED', 'SHIPPED'])->get();

		if (isset($ordersData) && !empty($ordersData)) {
			foreach ($ordersData as $key => $value) {
				$address = array();
				$address['street1'] = $value['sstreet1'];
				$address['street2'] = $value['sstreet2'];
				$address['street3'] = $value['sstreet3'];
				$address['city'] = $value['scity'];
				$address['state'] = $value['sstate'];
				$address['country'] = $value['scountry'];
				$address['postalCode'] = $value['spostalCode'];
				/** Remove Blanks Values */
				$address = array_filter($address);

				/** Array To string */
				$address = implode(', ', $address);

				/** URL encode the address */
				$address = urlencode($address);

				$apiKey = env('INSTALLTION_MAPKEY', 'AIzaSyC35k0LbzRptmwPWDcrOfnf9Tam1-I9dX8');

				/** google map geocode api url */
				$url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key=$apiKey";

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$responseJson = curl_exec($ch);
				curl_close($ch);

				$response = json_decode($responseJson);

				if ($response->status == 'OK') {
					$latitude = $response->results[0]->geometry->location->lat;
					$longitude = $response->results[0]->geometry->location->lng;

					$order = Orders::find($value['id']);
					$order->lat = $latitude;
					$order->long = $longitude;
					$order->save();
				}
			}
		}
	}

	function getRates($wherArr) {
		return Rate::where($wherArr)->pluck('name', 'id')->all();
	}

	function encryptDecryptUrlId($string, $action = "e") {
		$secret_key = ')o#}Ub[P?o`JNA1vy_Ov';
		$secret_iv = 'x2i.cZn"ne..Jr`B8kH';

		$output = false;
		$encrypt_method = "AES-256-CBC";
		$key = hash('sha256', $secret_key);
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		if ($action == 'e') {
			$output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
		} else if ($action == 'd') {
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}
		return $output;
	}

	function getTicketdata($orderId) {
		// $totalOrderQty = $this->getOrderItemQty($orderId);
		// if($totalOrderQty > 0){
		// 	$ticketsGeberatedFor = $this->getTicketItemsGenarted($orderId);
		// 	$pendingItemsForTicket = $totalOrderQty - $ticketsGeberatedFor;
		// 	if($pendingItemsForTicket > 0){
		$result = array();
		// $result['pendingQty'] = $pendingItemsForTicket;
		$result['pendingQty'] = 0;

		$installerWhere = [
			['status', 'ACTIVE'],
		];
		$result['installler'] = $this->getInstallationUser($installerWhere);

		return $result;
		// 	} else {
		// 		return false;
		// 	}
		// } else {
		// 	return false;
		// }
	}

	public function getInstallationUser($installerWhere) {
		$users = User::whereHas('roles', function ($query) {
			return $query->where('name', 'Installer');
		})->whereHas('rates', function ($query) {
			return $query->whereRaw('users_id IS NOT NULL');
		})->where($installerWhere)->pluck('users.name', 'users.id')->all();
		return $users;
	}

	public function getInstallationUserRate($installerRateWhere) {
		$installerRate = InstalerRate::with(['rateType'])
			->where($installerRateWhere)
			->whereHas('rateType', function ($query) {
				return $query->where('status', 'ACTIVE');
			})
			->get();
		return $installerRate;
	}

	private function getOrderItemQty($orderId) {
		$itemsQty = OrdersItems::select(
			DB::raw('SUM(quantity) as itemQty')
		)->where([
			'orders_id' => $orderId,
		])->groupBy('orders_id')->first();

		if (isset($itemsQty['itemQty']) && !empty($itemsQty['itemQty'])) {
			$orderItemsQty = $itemsQty['itemQty'];
		} else {
			$orderItemsQty = 0;
		}
		return $orderItemsQty;
	}

	private function getTicketItemsGenarted($orderId) {
		$ticketGeneratedQty = ServiceRequest::select(
			DB::raw('SUM(units) as itemQty')
		)->where([
			'orders_id' => $orderId,
			'status' => 'ACTIVE',
		])->groupBy('orders_id')->first();

		if (isset($ticketGeneratedQty['itemQty']) && !empty($ticketGeneratedQty['itemQty'])) {
			$ticketsGeberatedFor = $ticketGeneratedQty['itemQty'];
		} else {
			$ticketsGeberatedFor = 0;
		}

		return $ticketsGeberatedFor;
	}

	private function generateTicketNo($orderId) {
		$ticketGeneratedQty = ServiceRequest::select(
			DB::raw('SUM(units) as itemQty')
		)->where([
			'orders_id' => $orderId,
		])->groupBy('orders_id')->withTrashed()->first();

		if (isset($ticketGeneratedQty['itemQty']) && !empty($ticketGeneratedQty['itemQty'])) {
			$ticketsGeberatedFor = $ticketGeneratedQty['itemQty'];
		} else {
			$ticketsGeberatedFor = 0;
		}

		$ticketsCount = $ticketsGeberatedFor;

		$ticketsCount += 1;

		$order = Orders::select('order_number')->find($orderId);

		$ordernumber = (int) filter_var($order->order_number, FILTER_SANITIZE_NUMBER_INT);

		return $ordernumber . '-' . $ticketsCount;
	}

	public function generateTicket($postData) {
		$orderId = $postData['order_id'];
		// $totalOrderQty = $this->getOrderItemQty($orderId);
		// if($totalOrderQty > 0){
		// $ticketsGeberatedFor = $this->getTicketItemsGenarted($orderId);
		// $pendingItemsForTicket = $totalOrderQty - $ticketsGeberatedFor;
		// if($pendingItemsForTicket > 0){
		// if($pendingItemsForTicket >= $postData['units']){
		if ($postData['units'] > 0) {
			$ticket = new ServiceRequest;
			$ticket->ticket_no = $this->generateTicketNo($orderId);
			$ticket->orders_id = $orderId;
			$ticket->users_id = $postData['installer'];
			$ticket->service_type_id = $postData['serviceType'];
			$ticket->units = $postData['units'];
			$ticket->notes = $postData['notes'];
			$ticket->due_date = $postData['duedate'];
			$ticket->save();
			if ($ticket->exists()) {
				$userData = User::select([
					'name',
				])->where([
					'id' => $postData['installer'],
				])->first();

				$this->changeOrderStatusTicket($ticket->orders_id);
				$remark = "Service ticket is created ($ticket->ticket_no) and assigned to $userData->name";
				$log = array();
				$log['module'] = "SERVICE-TICKET";
				$log['module_id'] = $ticket->id;
				$log['department_id'] = null;
				$log['action'] = "NEW";
				$log['description'] = $remark;
				\LogActivity::addToLog($log);
				return $ticket;
			} else {
				throw new \Exception('Unable to save ticket data');
			}
		} else {
			throw new \Exception('Units should be greater than or equal to 1.');
		}
		// } else {
		// 	throw new \Exception('No of units cannot be greater than no of items in order.');
		// }
		// } else {
		// 	throw new \Exception('All items tickets are generated');
		// }
		// } else {
		// 	throw new \Exception('No items avaliable for create ticket');
		// }
	}

	public function generateHoldTicket($postData) {

		$ticketId = $postData['ticket_id'];
		$holdOn = Carbon::now()->isoFormat("YYYY-MM-DD");

		$ticket = HoldServiceTicket::firstOrNew(['ticket_id' => $ticketId]);
		$ticket->hold_until = $postData['hold_until'];
		$ticket->next_due_date = $postData['next_due_date'];
		$ticket->reason = $postData['reason'];
		$ticket->hold_by = $this->loggedInUserId();
		$ticket->save();
		if ($ticket) {
			$getTicketData = ServiceRequest::find($postData['ticket_id']);

			$getTicketData->hold_on = $holdOn;
			$getTicketData->due_date = $postData['next_due_date'];
			$getTicketData->stage = 'HOLD';
			$getTicketData->save();

			$userData = User::select([
				'name',
			])->where([
				'id' => $this->loggedInUserId(),
			])->first();

			$remark = "Service ticket ($getTicketData->ticket_no) is on hold on " . $holdOn . " by $userData->name until " . $postData['hold_until'] . " and its next due date is " . $postData['next_due_date'];
			$log = array();
			$log['module'] = "SERVICE-TICKET-HOLD";
			$log['module_id'] = $postData['ticket_id'];
			$log['department_id'] = null;
			$log['action'] = "HOLD";
			$log['description'] = $remark;
			\LogActivity::addToLog($log);
			return $ticket;
		} else {
			throw new \Exception('Unable to save hold ticket data');
		}

	}

	public function checkTicketHoldAge() {
		$getAllHoldTickets = HoldServiceTicket::whereDate('hold_until', now()->toDateString())->get();
		foreach ($getAllHoldTickets as $ticket) {
			$getticket = ServiceRequest::find($ticket->ticket_id);
			$getticket->stage = 'NEW TICKET';
			$getticket->save();
		}
	}

	function deleteTicket($ticketId) {
		$ticket = ServiceRequest::find($ticketId);
		if (isset($ticket) && !empty($ticket)) {
			$ticket->status = "INACTIVE";
			$ticket->save();
			$ticket->delete();
			return true;
		} else {
			throw new \Exception('No ticket found');
		}
	}

	function changeOrderStatusTicket($orderId, $status = "NEW TICKET") {
		$ticketCount = ServiceRequest::where([
			'orders_id' => $orderId,
			'status' => 'ACTIVE',
		])->count();

		if ($status == "PICKED UP") {
			$statusArr = ['NEW TICKET', 'PICKED UP'];
		} else if ($status == "INSTALLED") {
			$statusArr = ['NEW TICKET', 'PICKED UP', 'INSTALLED'];
		} else {
			$statusArr = ['NEW TICKET'];
			$status = 'ASSIGNED';
		}
		$ticketStatusCount = ServiceRequest::where([
			'orders_id' => $orderId,
			'status' => 'ACTIVE',
		])->whereIn('stage', $statusArr)->count();

		if ($ticketStatusCount == $ticketCount) {
			$order = Orders::find($orderId);
			$order->intsall_status = $status;
			$order->save();
		}

	}

	function getOrder($endpoint) {
		$response = $this->ss_get_curl($endpoint);
		$response = json_decode($response, true);
		return $response;
	}

	function removeEscalation() {
		$lastdate = Carbon::now()->subDays(7)->isoFormat("YYYY-MM-DD");
		$orders = Orders::where("escalate_updated_on", "<", $lastdate)->update([
			'escalate_created_on' => NULL,
			'escalate_created_by' => NULL,
			'escalate_updated_on' => NULL,
			'escalate_updated_by' => NULL,
			'escalate_update' => NULL,
			'escalate_request' => NULL,
			'escalate' => NULL,
		]);
	}

	function manageSkuNotes($notesCustomer) {
		$itemnotesArr = array();
		$notesCustomer = str_replace("*", "", $notesCustomer);
		$notesCustomer = str_replace("\r", "", $notesCustomer);
		$notes = array_filter(explode("\n", $notesCustomer));
		// dd($notes);
		$sku = "";
		foreach ($notes as $key => $value) {
			if (strpos($value, ") ")) {
				$itemSku = explode(") ", $value);
				$sku = trim($itemSku[1]);
				continue;
			}
			if (strpos(strtolower($value), "no notes")) {
				// continue;
				$value = "";
			}
			if ($sku == "") {
				continue;
			}
			$itemnotesArr[$sku][] = $value;
		}

		// dd($itemnotesArr);

		return $itemnotesArr;
	}

	function checkIsNotes($itemData) {

		$notes = array();

		if (isset($itemData) && !empty($itemData)) {
			foreach ($itemData as $key => $value) {
				$isNote = Notessku::where([
					'code' => $value['sku'],
				])->first();

				if (isset($isNote) && !empty($isNote)) {
					$notes[$value['sku']] = $value['name'];
				}
			}
		}

		// $notes = implode(", ", $notes);

		return $notes;
	}

	function addInternalNotes($notes, $itemId, $orderId) {
		$itemNote = new OrderInternalNote;
		$itemNote->orders_id = $orderId;
		$itemNote->orders_items_id = $itemId;
		$itemNote->note = $notes;
		$itemNote->user_id = 1;

		if ($itemNote->save()) {
			return true;
		} else {
			return false;
		}
	}
	public function shipLabel($shipApi) {
		$shipdata = OrderCreateShipment::where('id', $shipApi)->first();
		$shipdata->ss_status == 'INPROGRESS';
		$shipdata->save();
		$getCountryCode = Country::select('iso2')->where(['id' => $shipdata->country])->first();
		//dd($getCountryCode);
		$order = Orders::select(['order_id', 'status'])->where('id', $shipdata->order_id)->first();
		$ship["orderId"] = $order->order_id;
		$ship["carrierCode"] = $shipdata->carrierCode;
		$ship["serviceCode"] = $shipdata->serviceCode;
		$ship["packageCode"] = $shipdata->packageCode;
		$ship["shipDate"] = $shipdata->shipDate;
		$ship["weight"]["value"] = $shipdata->WeightUnits;
		$ship["weight"]["units"] = $shipdata->WeightValue;
		$ship["dimensions"]["length"] = $shipdata->length;
		$ship["dimensions"]["width"] = $shipdata->width;
		$ship["dimensions"]["height"] = $shipdata->height;
		$ship["dimensions"]["units"] = $shipdata->units;

		if (isset($shipdata->shipping_account) && !empty($shipdata->shipping_account)) {

			$ship["advancedOptions"]["billToParty"] = ($shipdata->shipping_account != 'third_party') ? 'my_account' : 'third_party';

			$ship["advancedOptions"]["billToAccount"] = $shipdata->account;

			$ship["advancedOptions"]["billToPostalCode"] = $shipdata->postal_code;

			$ship["advancedOptions"]["billToCountryCode"] = $getCountryCode->iso2;
		}

		if ($order->status == "PARTIALHOLD") {
			$orderItems = OrdersItems::select(['sku', 'name', 'orderItemId'])->where('orders_id', $shipdata->order_id)->where('status', 'COMPLETE')->get();
			if (isset($orderItems) && !empty($orderItems)) {
				$itemArray = [];
				foreach ($orderItems as $key => $value) {
					$itemArray['sku'] = $value['sku'];
					$itemArray['name'] = $value['name'];
					$itemArray['order_item_id'] = $value['orderItemId'];
				}
				$ship["advancedOptions"]["customField3"] = json_encode($itemArray);
			}
		}

		$res = $this->ss_post_curl('orders/createlabelfororder', json_encode($ship));
		$response = json_decode($res, true);

		$trackingNumber = isset($response['trackingNumber']) && !empty($response['trackingNumber']) ? $response['trackingNumber'] : null;
		\Log::channel('webhook_api')->critical($res);
		$data = json_decode($res, true);
		if (isset($data['shipmentId'])) {
			$arr = [
				'response' => $res,
				'ss_status' => 'SUCCESS',
				'labelData' => $data['labelData'],

			];
			$arrData = [
				'response' => $res,
				'ss_status' => 'SUCCESS',
				'labelData' => $data['labelData'],
				'trackingNumber' => $trackingNumber,
			];
			$updateship = OrderCreateShipment::where('id', $shipApi)->update($arr);

		} else {
			$arrData = [
				'response' => $res,
				'ss_status' => 'Failed',
			];
			\Log::debug('Shipment Error:' . $res);
		}

		return $arrData;
	}

	private function sendGrid($data)
    {
        // \Log::channel('api_logs')->info('SEND GRID SEND EMAIL STARTS');

        try {
            if (isset($data)) {

                $email = new \SendGrid\Mail\Mail();
                $email->setFrom($data['email_from']);
                $email->setSubject($data['subject']);
				$validatedToEmails = [];
				if(isset($data['email_to']) && !empty($data['email_to']))
				{
					foreach($data['email_to'] as $toEmailValidate)
					{
						if (filter_var($toEmailValidate, FILTER_VALIDATE_EMAIL)) 
						{
							$validatedToEmails[] = $toEmailValidate;
						}
					}
					
				}
                if (count($validatedToEmails) > 0) {
					foreach($validatedToEmails as $toEmail)
					{
                    	$email->addTo($toEmail);
					}
                    if (isset($data['cc']) && !empty($data['cc']) && filter_var($data['cc'], FILTER_VALIDATE_EMAIL)) {
                        $email->addCc($data['cc']);
                    }
                    if (isset($data['bcc']) && !empty($data['bcc']) && filter_var($data['bcc'], FILTER_VALIDATE_EMAIL)) {
                        $email->addBcc($data['bcc']);
                    }
                    $email->addContent("text/html", $data['message']);

                    
                    if (isset($data['files']) && !empty($data['files'])) {
						$data['files'] = json_decode($data['files'], true);
                        foreach ($data['files'] as $key => $value) {
                            $mimeType = mime_content_type($value);
                            $filename = basename($value);
                            $file_encoded = base64_encode(file_get_contents($value));
                            $email->addAttachment(
                                $file_encoded,
                                $mimeType,
                                $filename,
                                "attachment"
                            );
                        }
                    }

                    $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));
                    $result = $sendgrid->send($email);
                    //dd($result);
                    if ($result->statusCode() == 202) {
                        $response['success'] = true;
                        $response['response'] = $result;
                    } else {
                        throw new \Exception($result->body());
                    }

                } else {
                    throw new \Exception('To Email is not valid');
                }
            }
        } catch (\Exception $e) {
            $response['success'] = false;

            $response['response'] = $e->getMessage() . "\n";
        }

        return $response;
        \Log::channel('api_logs')->info('SEND GRID SEND EMAIL ENDS');

    }
}