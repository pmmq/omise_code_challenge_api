<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TumBoonController extends Controller
{

 	public function getAllCharity()
    {	
    	// get data from config data.php 
    	$data = json_decode(config('data.charities'));
    	if(empty($data)){
    		return response()->json($data, 200);
    	}
    	$data = $this->buildError("content not found.");
    	return response()->json($data,400);
    }
 
    public function donate(Request $request,$id)
    {

    	$amount = $request->get("donation_amount",0);
        $cardToken = $request->get("card_token","");
        $customerName = $request->get("customer_name","pubnaja");
        // convert decimal to integer
        $amount = number_format($amount, 2, '.', ' ') * 100;

    	if(!preg_match('/^[1-9][0-9]*$/',$id)){
    		$data = $this->buildError("invalid id.");
    		return response()->json($data,400);
    	}
        if($amount == 0){
    		$data = $this->buildError("invalid donation amount.");
    		return response()->json($data,400);
    	}
        if($cardToken == ''){
            $data = $this->buildError("invalid card token.");
            return response()->json($data,400);
        }
        if($customerName == 'pubnaja'){
            $data = $this->buildError("please fill customer name");
            return response()->json($data,400);
        }

        //build test card token
    	// $token = $this->createToken("name","4242424242424242",10,2018,"Bangkok",10210,123);
    	// $cardToken = $token->offsetGet('id');

        try {
            $charge = $this->chargeByCardToken($cardToken,$amount);
            $chargeError = $this->getFailedMessage($charge);
            if(!empty($chargeError)){
                $data = $this->buildError($chargeError["failure_message"],$chargeError["failure_code"]);
                return response()->json($data,400);
            }
        } catch (\OmiseExceptions $e) {
            $data = $this->buildError($e->getMessage());
            return response()->json($data,400);           
        }
        
        $data["success"] = true;
    	return response()->json($data,400);;
    }

    public function createToken($name, $number, $expiration_month, $expiration_year, $city, $postal_code, $security_code){
    	$param = array(
    		'card' => array(
	    			'name'   => $name,
				    'number' => $number,
				    'expiration_month'   => $expiration_month,
				    'expiration_year' => $expiration_year,
				    'city'   => $city,
				    'currency' => 'THB',
				    'postal_code'   => $postal_code,
				    'security_code' => $security_code,
			    )
    		);
    	$token = \OmiseToken::create($param);
    	return $token;
    }

    public function getFailedMessage($responObj){
        $error = null;
        if(!$responObj->offsetGet('failure_code') == null){
            $error["failure_code"] = $responObj->offsetGet('failure_code');
            $error["failure_message"] = $responObj->offsetGet('failure_message');
        }
        return $error;
    }

    public function chargeByCardToken($token, $amount, $currency = "THB"){
    	$charge = \OmiseCharge::create(array(
		    'amount'   => $amount,
		    'currency' => $currency,
		    'card'     => $token
		));
        return $charge;
    }

    public function buildError($message,$code = '400'){
    	$obj["error"] = $message;
        $obj["code"] = $code;
     	return $obj;
    }

}
