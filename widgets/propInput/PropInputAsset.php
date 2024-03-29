<?php

namespace thefx\blocks\widgets\propInput;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main frontend application asset bundle.
 */
class PropInputAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/assets';

    public $css = [
        'styles.css',
    ];

    public $js = [
        'script.js',
    ];

    public $depends = [
        'thefx\blocks\assets\SortableJs\SortableJsAsset',
        'yii\web\YiiAsset',
//        'yii\bootstrap4\BootstrapAsset',
//        'kartik\select2\Select2Asset',
        'thefx\blocks\assets\Select2Asset\Select2Asset',
    ];

    public $jsOptions = [
        'position' =>  View::POS_END,
    ];

    public function init()
    {
        \Yii::$app->view->registerCss('
            .sortable-chosen {outline: 0 !important}
            .sortable-chosen {border: 1px solid #3c8dbc}
            .image-wrapper .thumbnail  {cursor:move}
        ');

$js = <<<JS
    function enableImageGalleryDng(selector, propAssignmentId)
    {
        var el = document.querySelector(selector);
        var sortable = new Sortable(el, {
            draggable: ".thumbnail",  // Specifies which items inside the element should be draggable
            dataIdAttr: "data-key",
            ignore: ".thumbnail-new",
            // filter: ".btn",
            
            // Changed sorting within list
            onUpdate: function (/**Event*/evt) {
                var ids = [];
                el.querySelectorAll(".thumbnail").forEach(function(item, i, arr) {
//                  console.log($(item).data("key"));
                    ids.push($(item).data("key"));
                });
                $.ajax({
                    type: "POST",
                    url: "sort-photo-prop?id=" + propAssignmentId,
                    data: {ids:ids},
                    dataType: "json"
                });
            },
        });
    }
JS;

        /* @see https://github.com/RubaXa/Sortable */
        \Yii::$app->view->registerJs($js, View::POS_END);

        parent::init();
    }

}
