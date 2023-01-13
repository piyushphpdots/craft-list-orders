<?php
/**
 * List Orders plugin for Craft CMS 3.x
 *
 * List Orders
 *
 * @link      https://phpdots.com
 * @copyright Copyright (c) 2022 PHPDots Technologies
 */

namespace piyushphpdots\listorders\services;

use piyushphpdots\listorders\ListOrders;
use piyushphpdots\listorders\models\Order;
use piyushphpdots\listorders\models\OtherOrder;
use piyushphpdots\listorders\records\Order as OrderRecord;
use piyushphpdots\listorders\records\OtherOrder as OtherOrderRecord;

use Craft;
use craft\base\Component;

/**
 * OrderManager Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    PHPDots Technologies
 * @package   ListOrders
 * @since     1.0.0
 */
class OrderManager extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     ListOrders::$plugin->orderManager->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (ListOrders::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }

    public function getAllOrders()
    {
        $myResults = OrderRecord::find()->orderBy('id desc')->all();
        return $this->_recordsToModels($myResults);
    }

    public function getAllOtherOrders()
    {
        $myResults = OtherOrderRecord::find()->orderBy('id desc')->all();
        return $this->_otherRecordsToModels($myResults);
    }

    public function getOrderCount()
    {
        return OrderRecord::find()->count();
    }

    private function _recordsToModels($records)
    {
        foreach ($records as $key => $value) {
            $records[$key] = $this->_recordToModel($value);
        }
        return $records;
    }

    private function _otherRecordsToModels($records)
    {
        foreach ($records as $key => $value) {
            $records[$key] = $this->_otherRecordToModel($value);
        }
        return $records;
    }

    public function getCourseById($id)
    {
        $myResult = OrderRecord::find()->where(['id' => $id])->one();

        if (is_null($myResult)) {
            return null;
        }

        return $this->_recordToModel($myResult);
    }

    public function getOtherCourseById($id)
    {
        $myResult = OtherOrderRecord::find()->where(['id' => $id])->one();

        if (is_null($myResult)) {
            return null;
        }

        return $this->_otherRecordToModel($myResult);
    }

    private function _recordToModel($record)
    {
        return new Order($record->toArray([
            'id',
            'course_id',
            'course_name',
            'course_date',
            'price',
            'stripe_id',
            'first_name',
            'last_name',
            'house_number',
            'workshop_title',
            'customer_address',
            'postcode',
            'email',
            'phone',
            'description',
            'card_name',
            'dateCreated'
        ]));
    }

    private function _otherRecordToModel($record)
    {
        return new OtherOrder($record->toArray([
            'id',
            'course_id',
            'course_name',
            'course_date',
            'price',
            'first_name',
            'last_name',
            'house_number',
            'workshop_title',
            'customer_address',
            'postcode',
            'email',
            'phone',
            'description',
            'dateCreated'
        ]));
    }
}
