<?php

namespace ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use classes\classBundle\Classes\otakusClass;
use classes\classBundle\Classes\functionsClass;
use classes\classBundle\Entity\topics;
use classes\classBundle\Entity\posts;
use classes\classBundle\Entity\notifications;
class DefaultController extends Controller
{
    public function indexAction()
    {
    	$em = $this->getDoctrine()->getManager();
    	$repository = $em->getRepository('classesclassBundle:categories');
        $categories = $repository->findBy(array(),array("orderid" => "ASC"));

        foreach ($categories as $category)
        {
        	$repository = $em->getRepository('classesclassBundle:forums');
        	$category->forums = $repository->findBy(array("categoryid" => $category->id),array("orderid" => "ASC"));
        	if (!count($category->forums))
        	unset($category->forums);
        	else
        	foreach ($category->forums as $forum)
        	{      		
                $counts = $this->topicAndPostCounts($forum->id);
                $forum->topicCount = $counts['topicCount'];
                $forum->postCount = $counts['postCount'];

	    		$repository = $em->getRepository('classesclassBundle:topics');
	    		$lasttopic = $repository->findOneBy(array("forumid" =>$forum->id),array("lastModified" => "desc"));
	    		$repository = $em->getRepository('classesclassBundle:posts');
	    		$lastpost = $repository->findOneBy(array("forumid" =>$forum->id),array("id" => "desc"));	
	    		if ($lasttopic != null)
	    		{
		    		$forum->lastTopic = $lasttopic;
		    		$repository = $em->getRepository('classesclassBundle:otakus');
		    		$forum->lastActivity = $lastpost->dateCreated;
		    		$forum->lastPoster = $repository->findOneBy(array("id" =>$lastpost->otakuid));
	    		}	
        	}
        }
        return $this->render('ForumBundle:Default:index.html.twig',array("categories" => $categories) );
    }



    public function topicAndPostCounts($forumid)
    {
        $functionsClass = new functionsClass($this);
        $topicCount = $functionsClass->getCountById("topics",array("forumid" => $forumid));
        $postCount = $functionsClass->getCountById("posts",array("forumid" => $forumid));
        return array("topicCount" => $topicCount,"postCount" => $postCount);
    }
    public function inforumSearchAction($forumid = "all")
    {

        $connection = $this->get('doctrine.dbal.default_connection');
        $functionsClass = new functionsClass($this);
        $message = urldecode($functionsClass->escapeString($this->get("request")->query->get("search")));
        $pagenumber = $functionsClass->escapeString($this->get("request")->query->get("pagenumber",1));
        $forumid = $functionsClass->escapeString($forumid);
        $offset = $functionsClass->navigationOffset($pagenumber);

        if ($forumid == "all")
        $forumidsql = "";
        else
        $forumidsql = "AND topics.forumid = ".$forumid;
        //$forumidsql =  " AND (topics.forumid=".$forumid." AND posts.forumid = ".$forumid.')';



        $sql = 'SELECT topics.id as id, topics.title as title, posts.message as message,posts.id as postid  FROM topics LEFT join posts on topics.id = posts.topicid AND message LIKE "%'.$message.'%" WHERE (title LIKE "%'.$message.'%" OR message like "%'.$message.'%") '.$forumidsql.' LIMIT '.$offset.',10';

        $topics = $connection->executeQuery($sql)->fetchAll();

        foreach ($topics as &$topic)
        {
            if ($topic['message'] != null)
            {
                $sql = 'SELECT COUNT(*) as count FROM posts WHERE topicid = '.$topic['id'].' AND id <= '.$topic['postid'];
                $count2 =  $connection->executeQuery($sql)->fetchAll();
                $count2 = $count2[0]['count'];
                $topic['pagenumber'] = $functionsClass->navigationTotalPages($count2);
            }
        }
        $sql = 'SELECT COUNT(*) as count FROM topics LEFT join posts on topics.id = posts.topicid AND message LIKE "%'.$message.'%" WHERE (title LIKE "%'.$message.'%" OR message like "%'.$message.'%") '.$forumidsql;

        $count =  $connection->executeQuery($sql)->fetchAll();

        $count = $count[0]['count'];
        $totalPages = $functionsClass->navigationTotalPages($count);

        return $this->render('ForumBundle:Default:inforumsearch.html.twig',array("topics" => $topics,"count" => $count,"totalPages" => $totalPages,"pagenumber" => $pagenumber,"forumid" => $forumid,"search" => $message));
    }
    public function inforumAction($forumid,$title)
    {
        $em = $this->getDoctrine()->getManager();
        $functionsClass = new functionsClass($this);
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $pagenumber = $request->query->get("pagenumber",1);
        $repository = $em->getRepository('classesclassBundle:forums');
        $forum = $repository->findOneBy(array("id" => $forumid));
        $categoryid = $forum->categoryid;
        $offset = $functionsClass->navigationOffset($pagenumber);

        $totalPosts = $functionsClass->getCountById("posts",array("forumid" => $forumid));
        $totalTopics = $functionsClass->getCountById("topics",array("forumid" => $forumid));
        $totalPages = $functionsClass->navigationTotalPages($totalTopics);

        $repository = $em->getRepository('classesclassBundle:topics');
        $topics = $repository->findBy(array("forumid" => $forumid),array("lastModified" => "DESC"),10,$offset);
        
        
        foreach ($topics as $topic)
        {

            $topic->replies = $functionsClass->getCountById("posts",array("topicid" => $topic->id)) -1;
            $topic->totalPages = $functionsClass->navigationTotalPages($topic->replies+1);
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
        $searchByArray = array();


        $functionsClass->addField($searchByArray,"title","Title");
        $functionsClass->addField($searchByArray,"posts","Posts");

        $searchBy = $searchByArray['title'];
        return $this->render('ForumBundle:Default:inforum.html.twig',array("topics" => $topics,"title" => $title,"forumid" => $forumid,"categoryid" => $categoryid,"totalPosts" => $totalPosts,
            "totalTopics" => $totalTopics,"totalPages" => $totalPages,"pagenumber" => $pagenumber,"forum" => $forum,"searchByArray" => $searchByArray,"searchBy" => $searchBy) );

    
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
            $posts->message = str_replace('../../','/',$posts->message);
            $em->persist($posts);
            $em->flush();
            return new Response($this->generateUrl('forum_intopic',array("topicid" => $topics->id,"title" => $topics->title,"pagenumber"=> 1)));
        }
        return "";
    }

    public function intopicAction($topicid,$title)
    {
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $pagenumber = $request->query->get("pagenumber",1);
        $newpost = false;
        $em = $this->getDoctrine()->getManager();
        $otakusClass = new otakusClass($this);
        $functionsClass = new functionsClass($this);
        $totalPosts = $functionsClass->getCountById("posts",array("topicid" => $topicid));
        $totalPages = $functionsClass->navigationTotalPages($totalPosts);
        if ($request->query->get("newpost","") ==  "true")
        {
            $pagenumber = $totalPages;
            $newpost = true;
        }
        $offset = $functionsClass->navigationOffset($pagenumber);
        $repository = $em->getRepository('classesclassBundle:topics');
        $topic =  $repository->findOneBy(array("id" => $topicid));
        $repository = $em->getRepository('classesclassBundle:posts');
        $posts = $repository->findBy(array("topicid" => $topicid),array(),10,$offset);
        $repository = $em->getRepository('classesclassBundle:forums');
        $forum =  $repository->findOneBy(array("id" => $topic->forumid));
        foreach ($posts as $post)
        {
            $repository = $em->getRepository('classesclassBundle:otakus');
            $post->otaku = $repository->findOneBy(array("id" => $post->otakuid));
            $post->otaku->postCount = $otakusClass->getPostCount($post->otaku->id);
        }
        $params = array("posts" => $posts,"title" => $title,"topic" => $topic,"forum" => $forum,"totalPages" => $totalPages,"pagenumber" => $pagenumber,"newpost" => $newpost);
        if ($request->query->get("postid","") != "")
        $params['postid'] = $request->query->get("postid","");
        return $this->render('ForumBundle:Default:intopic.html.twig', $params);
    }
    public function intopicNewPostAction(Request $request)
    {
        $otakusClass = new otakusClass($this);
        if ($otakusClass->isRole("USER"))
        {
            $em = $this->getDoctrine()->getManager();
            $functionsClass = new functionsClass($this);
            $posts = new posts();
            $functionsClass->copyRequestObject($posts);
            $posts->otakuid = $otakusClass->getId();
            $posts->message = str_replace('../../../','/',$posts->message);

            $em->persist($posts);
            $repository = $em->getRepository('classesclassBundle:topics'); 
            $topic = $repository->findOneBy(array("id" => $posts->topicid)); 
            $topic->lastModified =  new \DateTime("now");
            $em->flush();

            $repository = $em->getRepository('classesclassBundle:subscriptions');
            $subscriptions =  $repository->findBy(array("type" => "topic","topicid" => $posts->topicid));

            //if ($topic->otakuid != $otakusClass->getId())
            $sendString = "";
            foreach ($subscriptions as $subscription)
            {
                if ($subscription->otakuid != $otakusClass->getId())
                {
                    $i = 0;
                    $notifictions = new notifications();
                    $notifictions->type = "forum_post";
                    $notifictions->postid =  $posts->id;
                    $notifictions->otakuid = $subscription->otakuid;
                    $em->persist($notifictions);
                    if ($i  % 100 == 0)
                    $em->flush();
                    $i++;
                    if ($sendString != "")
                    $sendString = $sendString.",";    
                    $sendString = $sendString.$subscription->otakuid;
                }
            }
            $em->flush();
            $response = array("url" => $this->generateUrl('forum_intopic',array("topicid" => $topic->id,"title" => $topic->title,"pagenumber"=> 1))."&newpost=true","ids" => $sendString);
            return new Response(json_encode($response));          
        }
        return new Response("");        
    }
}
