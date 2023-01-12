<?php
/**
 * List Orders plugin for Craft CMS 3.x
 *
 * List Orders
 *
 * @link      https://phpdots.com
 * @copyright Copyright (c) 2022 PHPDots Technologies
 */

namespace piyushphpdots\listorders\controllers;

use piyushphpdots\listorders\ListOrders;
use piyushphpdots\listorders\models\Order;
use piyushphpdots\listorders\models\OtherOrder;
use piyushphpdots\listorders\records\Order as OrderRecord;
use piyushphpdots\listorders\records\OtherOrder as OtherOrderRecord;

use Craft;
use craft\web\Controller;
use craft\web\View;
use Stripe\StripeClient;
use craft\elements\Entry;
use craft\elements\db\ElementQuery;
use craft\mail\Message;

/**
 * Order Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    PHPDots Technologies
 * @package   ListOrders
 * @since     1.0.0
 */
class FrontendController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/list-orders/order
     *
     * @return mixed
     */
    
    public function actionIndex()
    {
        $session = Craft::$app->getSession();
        $variables = [];
        $variables['courseId'] = $session->get('courseId');
        $variables['courseName'] = $session->get('courseName');
        $variables['coursePrice'] = $session->get('coursePrice');
        $variables['courseType'] = $session->get('courseType');
        $variables['entry'] = Entry::find()->id($session->get('courseId'))->one();

        if($session->get('courseType') == "cpd-course"){
            $html = \Craft::$app->view->renderTemplate('pages/booking/booking', $variables);
        }elseif($session->get('courseType') == "other-course"){
            $html = \Craft::$app->view->renderTemplate('pages/booking/other-booking', $variables);
        }else{
            $html = $this->redirect('/');
        }
        return $html;
    }

    public function actionPurchase()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $courseId = $request->getParam('courseId');
        $courseType = $request->getParam('courseType');
        $entry = Entry::find()->id($courseId)->one();

        $session = Craft::$app->getSession();
        $session->set("courseId", $courseId);
        $session->set("coursePrice", $entry->coursePrice);
        $session->set("courseName", $entry->title);
        $session->set("courseType", $courseType);

        return $this->redirect('course-booking');

    }

    public function actionBooking()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $courseId = $request->getParam('courseId');
        $courseDate = $request->getParam('workshop_title');
        $firstName = $request->getParam('first_name');
        $lastName = $request->getParam('last_name');
        $houseNumber = $request->getParam('house_number');
        $address = $request->getParam('address');
        $postcode = $request->getParam('postcode');
        $email = $request->getParam('email');
        $phone = $request->getParam('phone');
        $description = $request->getParam('description');
        $cardOwner = $request->getParam('card_owner');
        $stripeToken = $request->getParam('stripeToken');

        if($courseId == '' || $firstName == '' || $lastName == '' || $houseNumber == '' || $courseDate == '' || $address == '' || $postcode == '' || $email == '' || $phone == '' || $stripeToken == ''){
            return Craft::$app->session->setFlash('error', 'Please fill all the fields.');
        }
        
        $entry = Entry::find()->id($courseId)->one();
        $workshopTitle = $entry->title.' - '.date('d/m/Y', strtotime($courseDate));
        if($entry) {

            $query = 'email~"'.$email.'"';
            $stripe = new \Stripe\StripeClient([
                "api_key" => getenv('STRIPE_SECRET_KEY'),
                "stripe_version" => "2022-11-15"
            ]);
            try {
                $S_customer = $stripe->customers->search(['query' => $query, 'limit' => 100]);
            }catch (\Exception $e) {
                return Craft::$app->session->setFlash('error', $e->getMessage());
            }

            if(isset($S_customer->data) && count($S_customer->data) > 0) {
                $customer = $S_customer->data[0];
                $StripeCustomerId = $customer->id;
                try {
                    $stripe->customers->update( $StripeCustomerId, ['source' => $stripeToken] );
                } catch (\Exception $e) {
                    return Craft::$app->session->setFlash('error', $e->getMessage());
                }
            } else {
                $stripe = new \Stripe\StripeClient(getenv('STRIPE_SECRET_KEY'));
                try {
                    $CustomerOjb = $stripe->customers->create([
                        'email' => $email,
                        'source' => $stripeToken,
                    ]);
                    $StripeCustomerId = $CustomerOjb->id;
                } catch (\Exception $e) {
                    return Craft::$app->session->setFlash('error', $e->getMessage());
                }
            }

            if(!empty($StripeCustomerId)){
                try {
                    $charge = $stripe->charges->create([
                        'amount' => ($entry->coursePrice*100),
                        'currency' => 'GBP',
                        'customer' => $StripeCustomerId,
                        'description' => $description,
                    ]);            
                } catch (\Exception $e) {
                    return Craft::$app->session->setFlash('error', $e->getMessage());
                }

                if (!empty($charge) && $charge['status'] == 'succeeded') {
                    $record = new OrderRecord();
                    $record->dateCreated = date('Y-m-d H:i:s');
                    $record->dateUpdated = date('Y-m-d H:i:s');
                    $record->course_id = $courseId;
                    $record->course_name = $entry->title;
                    $record->course_date = $courseDate;
                    $record->price = $entry->coursePrice;
                    $record->stripe_id = $charge->id;
                    $record->first_name = $firstName;
                    $record->last_name = $lastName;
                    $record->house_number = $houseNumber;
                    $record->workshop_title = $workshopTitle;
                    $record->customer_address = $address;
                    $record->postcode = $postcode;
                    $record->email = $email;
                    $record->phone = $phone;
                    $record->description = $description;
                    //$record->card_name = $cardOwner;
                    $record->status = $charge->status;
                    $record->save();

                    $settings = Craft::$app->systemSettings->getSettings('email');
                    $message = new Message();
                    $html = '';
                    $variables = [];
                    $variables['firstName'] = $firstName ?? '';
                    $variables['lastName'] = $lastName ?? '';
                    $variables['houseNumber'] = $houseNumber ?? '';
                    $variables['workshopTitle'] = $workshopTitle ?? '';
                    $variables['address'] = $address ?? '';
                    $variables['postcode'] = $postcode ?? '';
                    $variables['email'] = $email ?? '';
                    $variables['phone'] = $phone ?? '';
                    $variables['description'] = $description ?? '';
                    $variables['courseName'] = $entry->title ?? '';
                    $variables['coursePrice'] = $entry->coursePrice ?? '';
                    $variables['courseDate'] = $courseDate ? date('d/m/Y', strtotime($courseDate)) : '';

                    $oldMode = Craft::$app->view->getTemplateMode();
                    Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
                    $html = Craft::$app->view->renderTemplate('list-orders/email/email', $variables);
                    Craft::$app->view->setTemplateMode($oldMode);
                    $message->setFrom([$settings['fromEmail'] => $settings['fromName']]);
                    $message->setTo($email);
                    //$message->setCc($email);
                    $message->setSubject('Course Booking');
                    $message->setHtmlBody($html);
                    $mail = Craft::$app->mailer->send($message);

                    return Craft::$app->session->setFlash('success', "Payment completed.");
                }
            }
        } else {
            return Craft::$app->session->setFlash('error', 'Please select course.');
        }
    }

    public function actionOtherbooking()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $courseId = $request->getParam('courseId');
        $courseDate = $request->getParam('workshop_title');
        $firstName = $request->getParam('first_name');
        $lastName = $request->getParam('last_name');
        $houseNumber = $request->getParam('house_number');
        $address = $request->getParam('address');
        $postcode = $request->getParam('postcode');
        $email = $request->getParam('email');
        $phone = $request->getParam('phone');
        $description = $request->getParam('description');
        
        if($courseId == '' || $firstName == '' || $lastName == '' || $houseNumber == '' || $courseDate == '' || $address == '' || $postcode == '' || $email == '' || $phone == ''){
            return Craft::$app->session->setFlash('error', 'Please fill all the fields.');
        }
        
        $entry = Entry::find()->id($courseId)->one();
        $workshopTitle = $entry->title.' - '.date('d/m/Y', strtotime($courseDate));
        if($entry) {
            $record = new OtherOrderRecord();
            $record->dateCreated = date('Y-m-d H:i:s');
            $record->dateUpdated = date('Y-m-d H:i:s');
            $record->course_id = $courseId;
            $record->course_name = $entry->title;
            $record->course_date = $courseDate;
            $record->price = $entry->coursePrice;
            $record->first_name = $firstName;
            $record->last_name = $lastName;
            $record->house_number = $houseNumber;
            $record->workshop_title = $workshopTitle;
            $record->customer_address = $address;
            $record->postcode = $postcode;
            $record->email = $email;
            $record->phone = $phone;
            $record->description = $description;
            $record->save();

            $settings = Craft::$app->systemSettings->getSettings('email');
            $message = new Message();
            $html = '';
            $variables = [];
            $variables['firstName'] = $firstName ?? '';
            $variables['lastName'] = $lastName ?? '';
            $variables['houseNumber'] = $houseNumber ?? '';
            $variables['workshopTitle'] = $workshopTitle ?? '';
            $variables['address'] = $address ?? '';
            $variables['postcode'] = $postcode ?? '';
            $variables['email'] = $email ?? '';
            $variables['phone'] = $phone ?? '';
            $variables['description'] = $description ?? '';
            $variables['courseName'] = $entry->title ?? '';
            $variables['coursePrice'] = $entry->coursePrice ?? '';
            $variables['courseDate'] = $courseDate ? date('d/m/Y', strtotime($courseDate)) : '';

            $oldMode = Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = Craft::$app->view->renderTemplate('list-orders/email/email', $variables);
            Craft::$app->view->setTemplateMode($oldMode);
            $message->setFrom([$settings['fromEmail'] => $settings['fromName']]);
            $message->setTo($email);
            //$message->setCc($email);
            $message->setSubject('Course Booking');
            $message->setHtmlBody($html);
            $mail = Craft::$app->mailer->send($message);

            return Craft::$app->session->setFlash('success', "Course booked.");
        } else {
            return Craft::$app->session->setFlash('error', 'Please select course.');
        }
    }
}
