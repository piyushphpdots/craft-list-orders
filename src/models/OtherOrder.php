<?php
/**
 * List Orders plugin for Craft CMS 3.x
 *
 * List Orders
 *
 * @link      https://phpdots.com
 * @copyright Copyright (c) 2022 PHPDots Technologies
 */

namespace piyushphpdots\listorders\models;

use piyushphpdots\listorders\ListOrders;

use Craft;
use craft\base\Model;

/**
 * Order Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    PHPDots Technologies
 * @package   ListOrders
 * @since     1.0.0
 */
class OtherOrder extends Model
{
    /**
     * @var integer
     */
    public $id = 0;
    public $course_id = 0;
    public $dateCreated = null;
    public $course_name = '';
    public $course_date = '';
    public $first_name = '';
    public $last_name = '';
    public $description = '';
    public $price = 0;
    public $house_number = 0;
    public $workshop_title = '';
    public $customer_address = '';
    public $postcode = '';
    public $email = '';
    public $phone = '';
}
