<?php

namespace ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use classes\classBundle\Classes\otakusClass;
use classes\classBundle\Classes\functionsClass;
use classes\classBundle\Entity\topics;
use classes\classBundle\Entity\posts;
class DefaultController extends Controller
{
    public function indexAction()
    {
    	$em = $this->getDoctrine()->getManager();
    	$repository = $em->getRepository('classesclassBundle:categories');
        $categories = $repository->findAll();

        foreach ($categories as $category)
        {
        	$repository = $em->getRepository('classesclassBundle:forums');
        	$category->forums = $repository->findBy(array("categoryid" => $category->id));
        	if (!count($category->forums))
        	unset($category->forums);
        	else
        	foreach ($category->forums as $forum)
        	{      		
                $counts = $this->topicAndPostCounts($forum->id);
                $forum->topicCount = $counts['topicCount'];
                $forum->postCount = $counts['postCount'];

	    		$repository = $em->getRepository('classesclassBundle:topics');
	    		$lasttopic = $repository->findOneBy(array("forumid" =>$forum->id),array("id" => "desc"));
	    		$repository = $em->getRepository('classesclassBundle:posts');
	    		$lastpost = $repository->findOneBy(array("forumid" =>$forum->id),array("id" => "desc"));	
	    		if ($lasttopic != null)
	    		{
		    		$forum->lastTitle = $lasttopic->title;
		    		$repository = $em->getRepository('classesclassBundle:otakus');
		    		$forum->lastActivity = $lastpost->dateCreated;
		    		$forum->lastPoster = $repository->findOneBy(array("id" =>$lastpost->otakuid));
	    		}	
        	}
        }
        return $this->render('ForumBundle:Default:index.html.twig',array("categories" => $categories) );
    }
    public function getCountById($table,$parameters)
    {
        $connection = $this->get('doctrine.dbal.default_connection');
        $parameterString = "";
        foreach ($parameters as $key => $value)
        {
            if ($parameterString != "")
            $parameterString = $parameterString ." AND ";
            $parameterString = $parameterString." ".$key."='".$value."'";
        }
        $sql = "SELECT  COUNT(*) AS  total FROM ".$table."  WHERE ".$parameterString;
        $countArray = $connection->executeQuery($sql)->fetch();
        return (int)$countArray['total'];;
    }
    public function topicAndPostCounts($forumid)
    {
        $topicCount = $this->getCountById("topics",array("forumid" => $forumid));
        $postCount = $this->getCountById("posts",array("forumid" => $forumid));
        return array("topicCount" => $topicCount,"postCount" => $postCount);
    }
    public function inforumAction($forumid,$title)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('classesclassBundle:forums');
        $forum = $repository->findOneBy(array("id" => $forumid));
        $categoryid = $forum->categoryid;


        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('classesclassBundle:topics');
        $topics = $repository->findBy(array("forumid" => $forumid),array("lastModified" => "DESC"));
        foreach ($topics as $topic)
        {
            $topic->replies = $this->getCountById("posts",array("topicid" => $topic->id)) -1;
            $repository = $em->getRepository('classesclassBundle:otakus');
            $topic->starter = $repository->findOneBy(array("id" => $topic->otakuid));
            if ($topic->replies > 0)
            {
                $repository = $em->getRepository('classesclassBundle:posts');
                $topic->lastPost = $repository->findOneBy(array("topicid" => $topic->id),array("id" => "DESC"));
                $repository = $em->getRepository('classesclassBundle:otakus');
                $topic->lastPostBy = $repository->findOneBy(array("id" => $topic->lastPost->otakuid));
            }
            else
            $topic->lastPostBy = $topic->starter;
        }
        if (count($topics) == 0)
        $topics = null;
        return $this->render('ForumBundle:Default:inforum.html.twig',array("topics" => $topics,"title" => $title,"forumid" => $forumid,"categoryid" => $categoryid) );
    }
    public function inforumPostNewTopicAction()
    {
        $otakusClass = new otakusClass($this);
        if ($otakusClass->isRole("USER"))
        {
            $em = $this->getDoctrine()->getManager();
            $functionsClass = new functionsClass($this);
            $topics = new topics();
            $functionsClass->copyRequestObject($topics);
            $topics->otakuid = $otakusClass->getId();
            $em->persist($topics);
            $em->flush();
            $posts = new posts();
            $functionsClass->copyRequestObject($posts);
            $posts->otakuid = $otakusClass->getId();
            $posts->topicid = $topics->id;
            $em->persist($posts);
            $em->flush();
        }
        return new Response("");
    }
}
