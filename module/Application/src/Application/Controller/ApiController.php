<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use Application\Entity\Post;
use \Exception;

class ApiController extends AbstractRestfulController
{

    public function create($data)
    {
        try {
            $em = $this->getEntityManager();
            $hydrator = $this->getHydrator();
            $post = $hydrator->hydrate($data, new Post());
            $em->persist($post);
            $em->flush();
            return new JsonModel(array('success' => true, 'data' => $hydrator->extract($post)));
        } catch (Exception $e) {
            return $this->returnError($e);
        }
    }

    public function update($id, $data)
    {
        try {
            $em = $this->getEntityManager();
            $post = $em->find('Application\Entity\Post', $id);
            if (!$post) {
                throw new Exception('Unable to find post with id ' . $id);
            }
            $hydrator = $this->getHydrator();
            $post = $hydrator->hydrate($data, $post);
            $em->persist($post);
            $em->flush();
            return new JsonModel($hydrator->extract($post));
        } catch (Exception $e) {
            return $this->returnError($e);
        }
    }

    public function delete($id)
    {
        try {
            $em = $this->getEntityManager();
            $post = $em->find('Application\Entity\Post', $id);
            if ($post) {
                $em->remove($post);
                $em->flush();
            } else {
                throw new Exception('Unable to find post with id ' . $id);
            }
            return new JsonModel(array('success' => true));
        } catch (Exception $e) {
            return $this->returnError($e);
        }
    }

    public function get($id)
    {
        try {
            $em = $this->getEntityManager();
            $post = $em->find('Application\Entity\Post', $id);
            if ($post) {
                $hydrator = $this->getHydrator();
                return new JsonModel($hydrator->extract($post));
            } else {
                throw new Exception('Unable to find post with id ' . $id);
            }
        } catch (Exception $e) {
            return $this->returnError($e);
        }
    }

    public function getList()
    {
        try {
            $q = $this->params()->fromQuery('q');
            $page = (int) $this->params()->fromQuery('page', 1);
            $size = (int) $this->params()->fromQuery('size', 15);
            $sortOrder = $this->params()->fromQuery('sort_order', 'title');
            $sortDesc = $this->params()->fromQuery('sort_desc', 'false');
            $desc = 'ASC';
            if ($sortDesc == 'true') {
                $desc = 'DESC';
            }

            $em = $this->getEntityManager();
            $qb = $em->getRepository('Application\Entity\Post')->createQueryBuilder('post');
            $qb->orderBy('post.' . $sortOrder, $desc);

            if (!is_null($q)) {
                $fields = array('post.id', 'post.title', 'post.body');
                $wheres = array();
                foreach ($fields as $field) {
                    $wheres[] = "$field like :q";
                }
                $qb->andWhere('(' . implode(' OR ', $wheres) . ')');
                $qb->setParameter('q', "%%{$q}%%");
            }

            $adapter = new DoctrineAdapter(new ORMPaginator($qb));
            $paginator = new Paginator($adapter);
            $paginator->setItemCountPerPage($size);
            $paginator->setCurrentPageNumber($page);

            $sql = $qb->getQuery()->getSQL();

            $results = array();
            $data = $paginator->getCurrentItems();
            $hydrator = $this->getHydrator();
            foreach ($data as $d) {
                $results[] = $hydrator->extract($d);
            }
            $totalCount = $paginator->getTotalItemCount();

            return new JsonModel(array('success' => true,
                'sql' => $sql,
                'count' => $totalCount,
                'page' => $page,
                'size' => $size,
                'data' => $results));
        } catch (Exception $e) {
            return $this->returnError($e);
        }
    }

    /**
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        return $em;
    }

    protected function getHydrator($entity = 'Application\Entity\Post')
    {
        $hydrator = new DoctrineObject($this->getEntityManager(), $entity);
        return $hydrator;
    }

    protected function returnError(Exception $e)
    {
        $this->getResponse()->setStatusCode(400);
        $this->getResponse()->setContent(json_encode(array('success' => false, 'errors' => array($e->getMessage()))));
        return $this->getResponse();
    }

}