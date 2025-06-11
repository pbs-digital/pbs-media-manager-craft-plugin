<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\assetbundles\mediamanager;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class MediaManagerAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = '@pbsdigital/mediamanager/assetbundles/mediamanager/dist';
        $this->depends    = [ CpAsset::class ];
        $this->js         = [ 'js/MediaManager.js' ];
        $this->css        = [ 'css/MediaManager.css' ];

        parent::init();
    }
}
