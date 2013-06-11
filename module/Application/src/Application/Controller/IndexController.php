<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {

        //$this->createRecords();
        return new ViewModel();
    }

    protected function createRecords()
    {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        for ($i = 1; $i <= 10000; $i++) {
            $post = new \Application\Entity\Post();
            $post->setTitle('This is the title for post # ' . $i);
            $post->setBody('This is the body for post # ' . $i);

            $em->persist($post);
            $em->flush();
        }
    }

}
