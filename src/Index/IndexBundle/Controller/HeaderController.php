<?php

namespace Index\IndexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use classes\classBundle\Entity\otakus;
use classes\classBundle\Entity\subscriptions;
use classes\classBundle\Entity\otakusImages;
use classes\classBundle\Classes\otakusClass;
use classes\classBundle\Classes\functionsClass;
class HeaderController extends Controller
{
    public function indexAction($currentuser)
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
        return $this->render("navigation.html.twig",array("categories" => $categories,"currentuser" => $currentuser));
    }
    public function privateMessagesCountAction()
    {
        $otakusClass = new otakusClass($this);        
        $messages = $this->getDoctrine()->getManager()->getRepository('classesclassBundle:privateMessages')->findBy(array("tootakuid" =>$otakusClass->getId(),"seen" => 0));
        return new Response(count($messages));
    }
    public function userAction()
    {
        $otakusClass = new otakusClass($this); 
        //var_dump($otakusClass->user);
        return new Response(json_encode($otakusClass->getSqlUser()));
    }
    public function useridAction()
    {
        $otakusClass = new otakusClass($this); 
        return new Response($otakusClass->getId());
    }
    public function returnSubscriptionAction($params)
    {
        $em = $this->getDoctrine()->getManager();
        $otakusClass = new otakusClass($this);
        $repository = $em->getRepository('classesclassBundle:subscriptions');
        $params = array_merge(array("otakuid" => $otakusClass->getId()),$params);
        $subscription =  $repository->findBy($params);
        if ($subscription != null)
        return new Response("selected");
        return new Response("");
    }
    function subscribeAction(Request $request)
    {
        $otakusClass = new otakusClass($this);
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('classesclassBundle:subscriptions');
        $params = $request->request->all();
        $params['otakuid'] = $otakusClass->getId();
        $subscription =  $repository->findOneBy($params);
        if ($subscription == null)
        {
            $subscription = new subscriptions();
            foreach ($params as $key => $value)
            $subscription->$key = $value;
            $em->persist($subscription);
        }
        else
        $em->remove($subscription);
        $em->flush();
        return new Response("");
    }

    public function otakuImagesAction()
    {
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $em = $this->getDoctrine()->getManager();
        $otakusClass = new otakusClass($this);
        $functionsClass = new functionsClass($this);
        $connection = $this->get('doctrine.dbal.default_connection');
        $pagenumber = $request->query->get("pagenumber",1);
        $otakuid = $otakusClass->getId();
        $offset = $functionsClass->navigationOffset($pagenumber);
        $repository = $em->getRepository('classesclassBundle:otakusImages');
        $images = $repository->findBy(array("otakuid" => $otakuid),array("id" => "DESC"),10,$offset);
        $count = $functionsClass->getCountById("otakusImages",array("otakuid" => $otakuid));
        $totalPages = $functionsClass->navigationTotalPages($count);

        return $this->render("otakusimages.html.twig", array("images" => $images,"totalPages" => $totalPages,"otakuid" => $otakuid,"pagenumber" => $pagenumber,"count" => $count));
    }
    public function otakuImagesUploadAction(Request $request)
    {
        $files = $_FILES;
        $path = "/var/www/web/UserImages/";
        $otakusClass = new otakusClass($this);
        $em = $this->getDoctrine()->getManager();
        foreach ($files as $key =>$uploadedFile) 
        {
            $otakusImages = new otakusImages();
            $imageProperties = @getimagesize($uploadedFile["tmp_name"]);

            if (!$imageProperties)
            return new Response("Bad image uploaded");

            $uploadedFile['name'] = filter_var($uploadedFile['name'],FILTER_SANITIZE_EMAIL);
            while(file_exists($path.$uploadedFile['name']))
            $uploadedFile['name'] = rand(0,9).$uploadedFile['name'];

            if (move_uploaded_file($uploadedFile["tmp_name"],
            $path.$uploadedFile["name"]))
            {
                $otakusImages->filename = $uploadedFile['name']; 
                $otakusImages->otakuid = $otakusClass->getId();
                $em->persist($otakusImages);
                $em->flush();
                return new Response("saved");
            }
        }
        
        return new Response("failed to upload");
    }
}
?>
