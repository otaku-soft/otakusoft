<?php

namespace profilesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use classes\classBundle\Classes\otakusClass;
use classes\classBundle\Classes\functionsClass;
class DefaultController extends Controller
{
	public $fields;
    public function viewProfileAction($id)
    {
    	$em = $this->getDoctrine()->getManager();
    	$repository = $em->getRepository('classesclassBundle:otakus');
    	$otaku = $repository->findOneBy(array("id" => $id));
    	$this->fields($otaku);
    	$functionsClass = new functionsClass($this);
    	$tabs = array();
    	$functionsClass->addfield($tabs,"viewprofilePersonalInformation","Info");
    	$functionsClass->addfield($tabs,"viewprofileTopics","Topics Started");
    	$functionsClass->addfield($tabs,"viewprofilePosts","Posts");
        return $this->render('profilesBundle:Default:viewprofile.html.twig',array("user" => $otaku,"fields" => $this->fields,"tabs" => $tabs));
    }

    function fields($otaku = null)
    {
    	$this->fields = array();
    	$functionsClass = new functionsClass($this);
    	$functionsClass->addfield($this->fields,"username","Username",array("type" => "text","minlength" => 5));
    	$functionsClass->addfield($this->fields,"avatar","Avatar",array("type" => "avatar")); 
    	$functionsClass->addfield($this->fields,"email","Email", array("type" => "text","email" => true,"profiledisplay" => false));
    	$functionsClass->addfield($this->fields,"aboutme","About Me",array("type" => "textarea"));
    	$functionsClass->addfield($this->fields,"hobbies","Hobbies",array("type" => "textarea"));
    	$functionsClass->addfield($this->fields,"favoriteAnimes","Favorite Animes",array("type" => "textarea"));
    	$functionsClass->addfield($this->fields,"favoriteGames","Favorite Games",array("type" => "textarea"));
    	$functionsClass->addfield($this->fields,"nyanPoints","Nyan Points",array("type" => "textarea"));
    	if ($otaku  != null)
    	{
    		foreach ($this->fields as &$field)
    		{
    			$fieldname =  $field->name; 
    			if (!in_array($fieldname,array("username","email")))
    			$field->value = $otaku->$fieldname;
    			else if ($fieldname == "username")
    			$field->value= $otaku->getUsername();
    			else if ($fieldname == "email")
    			$field->value= $otaku->getEmail();
    		}
    	}
    }
    function getTopicsAction()
    {
    	$em = $this->getDoctrine()->getManager();
    	$functionsClass = new functionsClass($this);
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $pagenumber = $request->query->get("pagenumber",1);
        $otakuid = $request->query->get("otakuid",0);
        $offset = $functionsClass->navigationOffset($pagenumber);
        $repository = $em->getRepository('classesclassBundle:topics');
        $topics = $repository->findBy(array("otakuid" => $otakuid),array("lastModified" => "DESC"),10,$offset);

        $count = $functionsClass->getCountById("topics",array("otakuid" => $otakuid));
        $totalPages = $functionsClass->navigationTotalPages($count);

        return $this->render('profilesBundle:Default:gettopics.html.twig',array("topics" => $topics,"totalPages" => $totalPages,"otakuid" => $otakuid,"pagenumber" => $pagenumber,"count" => $count));
    }
    function getPostsAction()
    {
    	$em = $this->getDoctrine()->getManager();
    	$functionsClass = new functionsClass($this);
    	$connection = $this->get('doctrine.dbal.default_connection');
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $pagenumber = $request->query->get("pagenumber",1);
        $otakuid = $request->query->get("otakuid",0);
        $offset = $functionsClass->navigationOffset($pagenumber);
        $repository = $em->getRepository('classesclassBundle:posts');
        $posts = $repository->findBy(array("otakuid" => $otakuid),array("id" => "DESC"),10,$offset);
        foreach ($posts as &$post)
        {
        	$repository = $em->getRepository('classesclassBundle:topics');
        	$post->topic = $repository->findOneBy(array("id" => $post->topicid));

            $sql = 'SELECT COUNT(*) as count FROM posts WHERE topicid = '.$post->topic->id.' AND id <= '.$post->id;
            $count2 =  $connection->executeQuery($sql)->fetchAll();
            $count2 = $count2[0]['count'];
            $post->pagenumber = $functionsClass->navigationTotalPages($count2);
        }

        $count = $functionsClass->getCountById("posts",array("otakuid" => $otakuid));
        $totalPages = $functionsClass->navigationTotalPages($count);

        return $this->render('profilesBundle:Default:getposts.html.twig',array("posts" => $posts,"totalPages" => $totalPages,"otakuid" => $otakuid,"pagenumber" => $pagenumber,"count" => $count));
    }
}
