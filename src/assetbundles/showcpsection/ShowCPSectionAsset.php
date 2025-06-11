<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\assetbundles\showcpsection;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ShowCPSectionAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = '@pbsdigital/mediamanager/assetbundles/showcpsection/dist';
        $this->depends    = [ CpAsset::class ];
        $this->js         = [ 'js/Show.js' ];
        $this->css        = [ 'css/Show.css' ];

        parent::init();
    }
}
