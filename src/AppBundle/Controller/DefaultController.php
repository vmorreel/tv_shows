<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/shows", name="shows")
     * @Template()
     */
    public function showsAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:TVShow');
        $res = $repo->findAll();
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate($res, $request->query->getInt('page', 1), 6);

        return [
        'shows' => $pagination
        ];
    }

    /**
     * @Route("/show/{id}", name="show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:TVShow');

        return [
        'show' => $repo->find($id)
        ];        
    }

    /**
     * @Route("/search", name="search")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $search = $request->get('search');
        $words = str_word_count($search, 1);

        // var_dump($words[0]);
        // die;

        if ($search) {
            $em = $this->get('doctrine')->getManager();
            $repo = $em->getRepository('AppBundle:TVShow');
            $query = $repo->createQueryBuilder('q')
            ->where('q.name LIKE :keyword')
            ->setParameter(':keyword', '%'. $words[0] . '%');

            for ($word=1; $word < count($words); $word++) { 
                $query->andWhere('q.name LIKE :keyword'.$word)
                ->setParameter(':keyword'.$word, '%'. $words[$word] . '%');
            }

            $query = $query->orderBy('q.name', 'ASC')->getQuery();
            $shows = $query->getResult();

            return [
            'shows' => $shows,
            'search' => $words
            ]; 
        }
        return $this->redirect($this->generateUrl('show'));
    }

    /**
     * @Route("/calendar", name="calendar")
     * @Template()
     */
    public function calendarAction()
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:Episode');

        $query = $repo->createQueryBuilder('e')
        ->innerJoin('e.season', 's')
        ->addSelect('s')
        ->innerJoin('s.show', 'sh')
        ->addSelect('sh')
        ->where('e.date >= CURRENT_DATE()')
        ->orderBy('e.date', 'ASC');

        $query = $query->getQuery();
        $episodes = $query->getResult();

        return [
        'episodes' => $episodes
        ];
    }

        /**
     * @Route("/login", name="login")
     * @Template()
     */
        public function loginAction()
        {
            return [];
        }
    }
