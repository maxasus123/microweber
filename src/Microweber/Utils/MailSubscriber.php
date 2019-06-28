<?php
namespace Microweber\Utils;

use MailerLiteApi\MailerLite;
use Finlet\flexmail\FlexmailAPI\FlexmailAPI;

class MailProvider
{
	protected $listTitle = '';
	protected $email = '';
	protected $firstName = '';
	protected $lastName = '';
	protected $phone = '';
	protected $address = '';
	protected $companyName = '';
	protected $companyPosition = '';
	protected $countryRegistration = '';
	protected $message = '';

	public function setListTitle($title) {
		$this->listTitle = $title;
	}
		
	public function setEmail($email) {
		$this->email = $email;
	}

	public function setFirstName($name) {
		$this->firstName = $name;
	}
	
	public function setLastName($name) {
		$this->lastName = $name;
	}

	public function setPhone($phone) {
		$this->phone = $phone;
	}
	
	public function setAddress($address) {
		$this->address = $address;
	}

	public function setCompanyName($name) {
		$this->companyName = $name;
	}

	public function setCompanyPosition($position) {
		$this->companyPosition = $position;
	}

	public function setCountryRegistration($country) {
		$this->countryRegistration = $country;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function submit() {
		$this->_flexmail();
		$this->_mailerLite();
	}
	
	private function _flexmail() {
		
		$settings = get_mail_provider_settings('flexmail');
	
		if (!empty($settings)) {
			
			try {
				$config = new \Finlet\flexmail\Config\Config();
				$config->set('wsdl', 'http://soap.flexmail.eu/3.0.0/flexmail.wsdl');
				$config->set('service', 'http://soap.flexmail.eu/3.0.0/flexmail.php');
				$config->set('user_id', $settings['api_user_id']);
				$config->set('user_token', $settings['api_user_token']); 
				$config->set('debug_mode', true);
				
				$flexmail = new \Finlet\flexmail\FlexmailAPI\FlexmailAPI($config);
				
				/* $categoryNames = array();
				foreach ($flexmail->service('Category')->getAll()->categoryTypeItems as $category){ 
					$categoryNames[] = $category->categoryName;
				} */
				
				/* if (!in_array($this->listTitle, $categoryNames)) {
					$response = $flexmail->service("Category")->create(array(
						'categoryName'=> $this->listTitle
					));
				}
				
				 */
				
				$contact = new \stdClass();
				$contact->emailAddress = $this->email;
				$contact->name = $this->firstName;
				$contact->surname = $this->lastName;
				$contact->phone = $this->phone;
				$contact->country = $this->countryRegistration;
				$contact->company = $this->companyName;
				$contact->address = $this->address;
				
				$response = $flexmail->service("Contact")->create(array(
					"mailingListId"    => 10000,
					"emailAddressType" => $contact
				)); 
				
			
			} catch (\Exception $e) {
				// Error
				dd($e);
			}
		}
	}
	
	private function _mailerLite() {
		
		$settings = get_mail_provider_settings('mailerlite');
		
		if (!empty($settings)) {
			
			try {
				$groupsApi = (new MailerLite($settings['api_key']))->groups();
				$allGroups = $groupsApi->get();
				
				$groupNames = array();
				foreach($allGroups as $group) {
					$groupNames[] = $group->name;
					$groupId = $group->id;
				}
				
				if (!in_array($this->listTitle, $groupNames)) {
					$newGroup = $groupsApi->create(['name' => $this->listTitle]);
					$groupId = $newGroup->id;
				}
				
				$subscribersApi = (new MailerLite($settings['api_key']))->subscribers();
				$allSubscribers = $subscribersApi->get();
				
				$subscriberEmails = array();
				foreach($allSubscribers as $subscriber) {
					$subscriberEmails[] = $subscriber->email;
				}
				
				if (!in_array($this->email, $subscriberEmails)) {
					$subscriber = [
						'email' => $this->email,
						'fields' => [
							'name' => $this->firstName,
							'last_name' => $this->lastName,
							'phone' => $this->phone,
							'company' => $this->companyName
						]
					];
					$groupsApi->addSubscriber($groupId, $subscriber);
				}
				
			} catch (\Exception $e) {
				// Error
			}
		}
	}
}