<?php
namespace fabiomlferreira\filemanager\filters;

/**
 * FrontendFilter is used to restrict access to yii2-file-manager controllers in frontend
 * for yii2 advanced template.
 * 
 * @author Fábio Ferreira
 */
class FrontendFilter extends \yii\base\ActionFilter
{
    /**
     * @var array
     */
    public $controllers = ['default', 'file'];
    
    /**
     * @param \yii\base\Action $action
     */
    public function beforeAction($action)
    {
        if (in_array($action->controller->id, $this->controllers)) {
            throw new \yii\web\NotFoundHttpException('Not found');
        }
        return true;
    }
}