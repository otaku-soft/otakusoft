<?php

namespace Index\IndexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use classes\classBundle\Entity\otakus;
use classes\classBundle\Classes\otakusClass;
use classes\classBundle\Classes\functionsClass;
class HeaderController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('classesclassBundle:contentCategories');
        $categories = $repository->findAll();

        foreach ($categories as &$category)
        {
            $repository = $em->getRepository('classesclassBundle:contentSections');
            $category->sections = $repository->findBy(array("contentCategoryid" => $category->id) );
            foreach ($category->sections as &$section)
            {
                $repository = $em->getRepository('classesclassBundle:contentSubSections');
                $section->subSection =  $repository->findBy(array("contentCategoryid" => $category->id,"contentSectionid" => $section->id) );
            }
        }
        return $this->render("navigation.html.twig",array("categories" => $categories));
    }
    public function privateMessagesCountAction()
    {
        $otakusClass = new otakusClass($this);        
        $messages = $this->getDoctrine()->getManager()->getRepository('classesclassBundle:privateMessages')->findBy(array("tootakuid" =>$otakusClass->getId(),"seen" => 0));
        return new Response(count($messages));
    }
    public function useridAction()
    {
        $otakusClass = new otakusClass($this); 
        return new Response($otakusClass->getId());
    }
}
?>
