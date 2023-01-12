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

use Craft;
use craft\web\Controller;

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
class OrderController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index', 'do-something'];

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
        $variables = [];
        $variables['orders'] = ListOrders::$plugin->orderManager->getAllOrders();
        return $this->renderTemplate('list-orders/order/index', $variables);
    }

    public function actionOtherOrder()
    {
        $variables = [];
        $variables['orders'] = ListOrders::$plugin->orderManager->getAllOtherOrders();
        return $this->renderTemplate('list-orders/other-order/index', $variables);
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/list-orders/order/do-something
     *
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'Welcome to the OrderController actionDoSomething() method';

        return $result;
    }

    public function actionShowEdit($courseId = null, $course = null)
    {
        $variables = [];
        if(!$course){
            if ($courseId) {
                $course = ListOrders::$plugin->orderManager->getCourseById($courseId);
                $variables['course'] = $course;
            } else {
                $variables['course'] = new Course();
            }
        }else{
            $variables['course'] = $course;
        }

        return $this->renderTemplate('list-orders/order/edit', $variables);
    }

    public function actionOtherShowEdit($courseId = null, $course = null)
    {
        $variables = [];
        if(!$course){
            if ($courseId) {
                $course = ListOrders::$plugin->orderManager->getOtherCourseById($courseId);
                $variables['course'] = $course;
            } else {
                $variables['course'] = new Course();
            }
        }else{
            $variables['course'] = $course;
        }

        return $this->renderTemplate('list-orders/other-order/edit', $variables);
    }

    public function actionDelete()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $courseId = $request->getParam('courseId');

        $result = ListOrders::$plugin->orderManager->deleteCourseById($courseId);
    }
}
