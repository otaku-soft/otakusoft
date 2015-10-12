<?php

namespace notificationsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use classes\classBundle\Classes\otakusClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use classes\classBundle\Classes\functionsClass;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$functionsClass = new functionsClass($this);
    	$otakusClass = new otakusClass($this);
    	$repository = $em->getRepository('classesclassBundle:notifications');
    	$pagenumber = $request->query->get("pagenumber",1);
    	$connection = $this->get('doctrine.dbal.default_connection');
    	$offset = $functionsClass->navigationOffset($pagenumber);
        $notifications = $repository->findBy(array("otakuid" => $otakusClass->getId()),array("id" => "DESC"),10,$offset);
        $totalPages = $functionsClass->navigationTotalPages($functionsClass->getCountById("notifications",array("otakuid" => $otakusClass->getId())));
        foreach ($notifications as &$notification)
        {
        	if ($notification->type == "forum_post")
        	{
        		$repository = $em->getRepository('classesclassBundle:posts');
        		$post = $repository->findOneBy(array("id" => $notification->postid));
        		$repository = $em->getRepository('classesclassBundle:topics');
        		$topic = $repository->findOneBy(array("id" => $post->topicid));
        		$repository = $em->getRepository('classesclassBundle:otakus');
        		$otaku = $repository->findOneBy(array("id" => $post->otakuid));

                $sql = 'SELECT COUNT(*) as count FROM posts WHERE topicid = '.$topic->id.' AND id <= '.$post->id;
                $count2 =  $connection->executeQuery($sql)->fetchAll();
                $count2 = $count2[0]['count'];
                $topic->pagenumber = $functionsClass->navigationTotalPages($count2);


        		$notification->html = '<span style = "color:black"><b><a href = "'.$this->generateUrl('profiles_viewprofile',array("id" =>$post->otakuid )).'">'.$otaku->getUsername().'</a></b> has replied to your topic: <b><a href = "'.$this->generateUrl('forum_intopic',array("topicid" =>$topic->id,"title" => $topic->title,"pagenumber" => $topic->pagenumber,"postid" => $post->id )).'">'.$topic->title.'</a></b></span>';
        		$notification->orgseen = $notification->seen;
        		$notification->seen = 1;
        	}
        }
        $em->flush();
        return $this->render('notificationsBundle:Default:index.html.twig',array("notifications" => $notifications,"pagenumber" => $pagenumber,"totalPages" => $totalPages,"pagination" => array(),"paginationPath" => "notifications_messages"));
    }
    public function notificationsCountAction()
    {
    	$functionsClass = new functionsClass($this);
    	$otakusClass = new otakusClass($this);
    	return new Response ($functionsClass->getCountById("notifications",array("otakuid" => $otakusClass->getId(),"seen" => 0)));
    }
}