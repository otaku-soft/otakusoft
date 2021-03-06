<?php
namespace profilesBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use classes\classBundle\Entity\friends;
use classes\classBundle\Entity\visitorMessages;
use classes\classBundle\Entity\notifications;
class DefaultController extends Controller
{
	public $fields;
    public function viewProfileAction($id)
    {
    	$em = $this->getDoctrine()->getManager();
        $otakusClass = $this->get("otakuClass");
        $request = Request::createFromGlobals();
        $request->getPathInfo();
    	$repository = $em->getRepository('classesclassBundle:otakus');
    	$otaku = $repository->findOneBy(array("id" => $id));
        $friendotakuid = $otakusClass->getId();
        $repository = $em->getRepository('classesclassBundle:friends');
        $friend = $repository->findOneBy(array("otakuid" => $id,"friendotakuid" => $friendotakuid));
        if ($friend != null)
        $friend = true;
    	$this->fields($otaku);
    	$functionsClass = $this->get("functionsClass");
    	$tabs = array();
    	$functionsClass->addfield($tabs,"viewprofilePersonalInformation","Info");
    	$functionsClass->addfield($tabs,"viewprofileTopics","Topics Started");
    	$functionsClass->addfield($tabs,"viewprofilePosts","Posts");
        $functionsClass->addfield($tabs,"viewprofileFriends","Friends");   
        $functionsClass->addfield($tabs,"viewprofileVistorMessages","Visitor Messages"); 
        $section =   $request->query->get("section");
        if ($otakusClass->getId() == $id)
        $self = true;
        else
        $self = false;

        return $this->render('profilesBundle:Default:viewprofile.html.twig',array("user" => $otaku,"fields" => $this->fields,"tabs" => $tabs,"friend" => $friend,"section" => $section,"self" => $self));
    }
    public function addFriendButtonAction()
    {
        $otakusClass = $this->get("otakuClass");
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        if ($otakusClass->isRole("USER") && $otakusClass->getId() != $request->request->get('friendotakuid',""))
        {
            $repository = $this->getDoctrine()->getManager()->getRepository('classesclassBundle:friends');
            $friend = $repository->findOneBy(array("otakuid" => $otakusClass->getId(),"friendotakuid" => $request->request->get('friendotakuid',"")));
            return $this->render('profilesBundle:Default:addfriendbutton.html.twig',array("friend" => $friend,"friendotakuid" => $request->request->get('friendotakuid',"")));
        }
        return new Response("");
    }
    public function fillInFriendDataAction()
    {
        for ($i = 1; $i < 1000; $i++)
        {
            $profiles = new friends();
            $profile->otakuid = $i;
            $profile->friendotakuid = $i;
            $profile->status = $i;
            $em = $this->getDoctrine()->getManager();
            $em->persist($profiles);
            $em->flush();
        }
    }
    public function indexAction()
    {
        $functionsClass = $this->get("functionsClass");
        $em = $this->getDoctrine()->getManager();
        $connection = $this->get('doctrine.dbal.default_connection');
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $otakusClass = $this->get("otakuClass");
        $pagenumber = $request->query->get("pagenumber",1);
        $offset = $functionsClass->navigationOffset($pagenumber);
        $repository = $em->getRepository('classesclassBundle:otakus');
        $otakus = $repository->findBy(array(),array(),10,$offset);

        $search = $functionsClass->escapeString($request->query->get("search",""));
        $whereSql = "";
        if ($search != "")
        $whereSql = "WHERE username LIKE '%".$search."%'";

        $sql = 'SELECT * FROM otakus '.$whereSql.' LIMIT '.$offset.',10';
        $otakus = $connection->executeQuery($sql)->fetchAll();
        foreach ($otakus as &$otaku)
        {
            $otaku['postcount'] = $otakusClass->getPostCount($otaku['id']);
        }
        $sql = 'SELECT COUNT(*) as total FROM otakus '.$whereSql;
        $data = $connection->executeQuery($sql)->fetch();

        $count = $data['total'];

        $totalPages = $functionsClass->navigationTotalPages($count);
        return $this->render('profilesBundle:Default:index.html.twig',array("otakus" => $otakus,"pagenumber" => $pagenumber,"totalPages" => $totalPages,"search" => $search,"count" => $count));
    }

    public function editProfileAction()
    {
        $otakusClass = $this->get("otakuClass");
        $functionsClass = $this->get("functionsClass");
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        if ($otakusClass->isRole("USER"))
        {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('classesclassBundle:otakus');
            $otaku = $repository->findOneBy(array("id" => $otakusClass->getId()));
            $this->fields($otaku);
            $tabs = array();
            $functionsClass->addfield($tabs,"editprofilePersonal","Personal");
            $functionsClass->addfield($tabs,"editprofileFriends","Friends");
            $functionsClass->addfield($tabs,"editprofileFriendRequests","Friend Requests");
            $section =   $request->query->get("section");
            return $this->render('profilesBundle:Default:editprofile.html.twig',array("user" => $otaku,"fields" => $this->fields,"tabs" => $tabs,"section" => $section));
        }
        return new Response("not auth");
    }
    public function sendFriendRequestAction()
    {
        $otakusClass = $this->get("otakuClass");
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        if ($otakusClass->isRole("USER"))
        {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('classesclassBundle:friends');
            $checkFriendRequest = $repository->findOneBy(array("otakuid" => $otakusClass->getId(),"friendotakuid" => $request->request->get("id") ));
            $checkFriendRequest2 = $repository->findOneBy(array("otakuid" =>$request->request->get("id"),"friendotakuid" => $otakusClass->getId() ));
            
            if ($checkFriendRequest2 != null)
            {
                $friends = new friends();
                $friends->otakuid = $otakusClass->getId();
                $friends->friendotakuid = $request->request->get("id");
                $friends->status = 1;
                $em->persist($friends);
                $checkFriendRequest2->status = 1;
                $em->flush();
                $notificaitons = new notifications();
                $notificaitons->friendRequestAcceptedOtakuid = $otakusClass->getId();
                $notificaitons->type = "forum_friend_added";
                $notificaitons->otakuid = $request->request->get("id");  
                $em->persist($notificaitons);
                $em->flush();
                return new Response('Saved');
            }
            if ($checkFriendRequest == null)
            {
                $friends = new friends();
                $friends->otakuid = $otakusClass->getId();
                $friends->friendotakuid = $request->request->get("id");
                $friends->status = 0;
                $em->persist($friends);
                $em->flush();
                $notificaitons = new notifications();
                $notificaitons->friendRequestSendOtakuid = $otakusClass->getId();
                $notificaitons->type = "forum_friend_request";
                $notificaitons->otakuid = $request->request->get("id");      
                $em->persist($notificaitons);
                $em->flush();
                return new Response('Saved');
            }
            else
            return new Response('Friend Request Already Submitted');

        }
        return new Response("not auth");
    }
    public function removeFriendAction()
    {
        $otakusClass = $this->get("otakuClass");
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        if ($otakusClass->isRole("USER"))
        {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('classesclassBundle:friends');
            $checkFriendRequest = $repository->findOneBy(array("otakuid" => $otakusClass->getId(),"friendotakuid" => $request->request->get("id") ));
            $checkFriendRequest2 = $repository->findOneBy(array("otakuid" =>$request->request->get("id"),"friendotakuid" => $otakusClass->getId() ));
            if ($checkFriendRequest != null)
            {
                $em->remove($checkFriendRequest);
            }
            if ($checkFriendRequest2 != null)
            {
                $em->remove($checkFriendRequest2);
            }
            $em->flush();
        }
        return new Response("");
    }
    public function editProfileSaveAction()
    {
        $otakusClass = $this->get("otakuClass");
        $functionsClass =$this->get("functionsClass");
        if ($otakusClass->isRole("USER"))
        {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('classesclassBundle:otakus');
            $otaku = $repository->findOneBy(array("id" => $otakusClass->getId()));

            $request = Request::createFromGlobals();
            $request->getPathInfo();

            $arr =  $request->request->all();
            while (list($key, $value) = each($arr))
            {
               
                $otaku->$key = str_replace('../../','/',$value);

            }
            $files = $_FILES;
            $path =  getcwd()."/avatars/";
            $urlpath = "/avatars/";
            
            foreach ($files as $key =>$uploadedFile) 
            {
                if (strlen($uploadedFile['name']) > 100 )
                return new Response("File Name too long");
                if ($uploadedFile['name'] != "")
                {
                    $imageProperties = @getimagesize($uploadedFile["tmp_name"]);

                    if (!$imageProperties)
                    return new Response("Bad image uploaded");


                    $width  = $imageProperties[0];
                    $height = $imageProperties[1];
                    $orgWidth = $width;
                    $orgHeight = $height;
                    if ($width > 200 || $height > 200)
                    {
                        if ($width > 200)
                        {
                            $width = 200;
                            $ratio = $width/$orgWidth;
                            $height = $orgHeight * $ratio;
                        }
                        if ($height > 200)
                        {
                            $height = 200;
                            $ratio = $height/$orgHeight;
                            $width = $orgWidth * $ratio;
                        }                
                        
                        $file = $uploadedFile["tmp_name"];
                        $image = new \Imagick($file);
                        $image->thumbnailImage($width, $height);
                        $image->writeImage($file);
                    }

                    
                    
                    $uploadedFile['name'] = filter_var($uploadedFile['name'],FILTER_SANITIZE_EMAIL);
                    while(file_exists($path.$uploadedFile['name']))
                    $uploadedFile['name'] = rand(0,9).$uploadedFile['name'];

                    if (move_uploaded_file($uploadedFile["tmp_name"],
                    $path.$uploadedFile["name"]))
                    $otaku->avatar = $uploadedFile['name'];
                
                }
                
            }
            $em->flush();
            return new Response("saved");
        }
        return new Response("not auth");
    }
    function fields($otaku = null)
    {
    	$this->fields = array();
    	$functionsClass = $this->get("functionsClass");
    	$functionsClass->addfield($this->fields,"avatar","Avatar",array("type" => "avatar")); 
    	$functionsClass->addfield($this->fields,"email","Email", array("type" => "text","email" => true,"profiledisplay" => false));
    	$functionsClass->addfield($this->fields,"aboutme","About Me",array("type" => "textarea"));
    	$functionsClass->addfield($this->fields,"hobbies","Hobbies",array("type" => "textarea"));
    	$functionsClass->addfield($this->fields,"favoriteAnimes","Favorite Animes",array("type" => "textarea"));
    	$functionsClass->addfield($this->fields,"favoriteGames","Favorite Games",array("type" => "textarea"));
    	$functionsClass->addfield($this->fields,"nyanPoints","Nyan Points",array("type" => "nyanPoints"));
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
    	$functionsClass = $this->get("functionsClass");
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
    	$functionsClass = $this->get("functionsClass");
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
    function getVisitorsMessagesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $functionsClass = $this->get("functionsClass");
        $connection = $this->get('doctrine.dbal.default_connection');
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $pagenumber = $request->query->get("pagenumber",1);
        $otakuid = $request->query->get("otakuid",0);

        $offset = $functionsClass->navigationOffset($pagenumber);
        $repository = $em->getRepository('classesclassBundle:visitorMessages');
        $messages = $repository->findBy(array("otakuid" => $otakuid),array("id" => "DESC"),10,$offset);
        $repository = $em->getRepository('classesclassBundle:otakus');
        foreach ($messages as &$message)
        {
            $message->otaku =  $repository->findOneBy(array("id" => $message->friendotakuid));
        }
        $count = $functionsClass->getCountById("visitorMessages",array("otakuid" => $otakuid));
        $totalPages = $functionsClass->navigationTotalPages($count);

        return $this->render('profilesBundle:Default:getvisitormessages.html.twig',array("messages" => $messages,"totalPages" => $totalPages,"otakuid" => $otakuid,"pagenumber" => $pagenumber,"count" => $count));
    }
    function addVisitorMessageAction()
    {
        $em = $this->getDoctrine()->getManager();
        $otakusClass = $this->get("otakuClass");
        $visitorMessages = new visitorMessages();
        $friendotakuid = $otakusClass->getId();
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $otakuid = $request->request->get("otakuid",0);
        $message= str_replace('../../','/',$request->request->get("message",0));
        $visitorMessages->otakuid = $otakuid;
        $visitorMessages->friendotakuid = $friendotakuid;
        $visitorMessages->message = $message;
        $em->persist($visitorMessages);
        $em->flush();
        $notificaitons = new notifications();
        $notificaitons->otakuid = $otakuid;
        $notificaitons->visitormessageid = $visitorMessages->id;
        $notificaitons->type = "forum_visitor_message";
        $em->persist($notificaitons);
        $em->flush();       
        return new Response("");
    }
    function getFriendsAction()
    {
        return new Response("friends");
    }
}
