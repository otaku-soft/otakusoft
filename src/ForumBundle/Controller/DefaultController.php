<?php

namespace ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
    	$em = $this->getDoctrine()->getManager();
    	$repository = $em->getRepository('classesclassBundle:categories');
        $categories = $repository->findAll();
        $connection = $this->get('doctrine.dbal.default_connection');

        foreach ($categories as $category)
        {
        	$repository = $em->getRepository('classesclassBundle:forums');
        	$category->forums = $repository->findBy(array("categoryid" => $category->id));
        	if (!count($category->forums))
        	unset($category->forums);
        	else
        	foreach ($category->forums as $forum)
        	{      		
        		$topicsCountSql = "SELECT  COUNT(*) AS total FROM topics WHERE forumid = ".$forum->id;
        		$postsCountSql = "SELECT  COUNT(*) AS total FROM posts WHERE forumid = ".$forum->id;
        		$topicsCountArray = $connection->executeQuery($topicsCountSql)->fetch();
        		$postsCountArray = $connection->executeQuery($postsCountSql)->fetch();
        		$forum->topicCount = (int)$topicsCountArray['total'];
        		$forum->postCount = (int)$topicsCountArray['total'] + (int)$postsCountArray['total'];


	    		$repository = $em->getRepository('classesclassBundle:topics');
	    		$lasttopic = $repository->findOneBy(array("forumid" =>$forum->id),array("id" => "desc"));
	    		$repository = $em->getRepository('classesclassBundle:posts');
	    		$lastpost = $repository->findOneBy(array("forumid" =>$forum->id),array("id" => "desc"));	
	    		if ($lasttopic != null)
	    		{
		    		$forum->lastTitle = $lasttopic->title;
		    		$repository = $em->getRepository('classesclassBundle:otakus');
		    		if ( $lastpost == null || $lasttopic->dateCreated > $lastpost->dateCreated)
		    		{
		    			$forum->lastActivity = $lasttopic->dateCreated;
		    			$forum->lastPoster = $repository->findOneBy(array("id" =>$lasttopic->otakuid));
	    			}
		    		else
		    		{
			    		$forum->lastActivity = $lastpost->dateCreated;
			    		$forum->lastPoster = $repository->findOneBy(array("id" =>$lastpost->otakuid));
		    		}
	    		}	
        	}
        }
        return $this->render('ForumBundle:Default:index.html.twig',array("categories" => $categories) );
    }
}
