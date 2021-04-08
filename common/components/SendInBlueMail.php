<?php
namespace Common\Components;

use Phalcon\Mvc\User\Component;
use Common\Models\IPhoneCode;
use Common\Components\Log;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;

class SendInBlueMail extends Component {

    public function sendCode($email,$code){
       
        # Instantiate the client\
        \SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey("api-key", "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");

        $api_instance = new \SendinBlue\Client\Api\EmailCampaignsApi();
        $emailCampaigns = new \SendinBlue\Client\Model\CreateEmailCampaign();

        # Define the campaign settings
        $email_campaigns['name'] = "Campaign sent via the API";
        $email_campaigns['subject'] = "My subject";
        $email_campaigns['sender'] = [
            "name"=> "From name", 
            "email"=>"info@olemart.es"
        ];
        $email_campaigns['type'] = "classic";
        $email_campaigns['htmlContent'] = "Congratulations! You successfully sent this example campaign via the SendinBlue API.";
        // $email_campaigns['recipients'] = array("listIds"=> [2, 7]);

        try {
            $result = $api_instance->createEmailCampaign($emailCampaigns);
            return $result;
            // print_r($result);
        } catch (\Exception $e) {
            echo 'Exception when calling EmailCampaignsApi->createEmailCampaign: ', $e->getMessage(), PHP_EOL;
        }
    }



	
}
