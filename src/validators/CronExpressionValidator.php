<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\validators;

use Craft;
use yii\validators\Validator;
use Cron\CronExpression;

class CronExpressionValidator extends Validator
{
    // Public Methods
    // =========================================================================

    public function validateAttribute( $model, $attribute )
    {
        $expression = $model->$attribute;

        if( !empty( $expression ) && !CronExpression::isValidExpression( $expression ) ) {
            $this->addError( $model, $attribute, 'Invalid cron format, please read CRON documentation for valid format.' );
        }

        return;
    }
}
