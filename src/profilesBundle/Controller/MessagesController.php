<?php

namespace profilesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use classes\classBundle\Classes\otakusClass;
use classes\classBundle\Classes\functionsClass;
use classes\classBundle\Entity\friends;
use classes\classBundle\Entity\privateMessages;
class MessagesController extends Controller
{

    public function indexAction()
    {
    	$otakusClass = new otakusClass($this);
        $functionsClass = new functionsClass($this);

        $messages = $this->getDoctrine()->getManager()->getRepository('classesclassBundle:privateMessages')->findBy(array("tootakuid" =>$otakusClass->getId(),"seen" => 0));
        foreach ($messages as $message)
        {
            $message->seen = 1;
        }
        $this->getDoctrine()->getManager()->flush();

    	if ($otakusClass->isRole("USER"))
        {
            $tabs = array();
            $functionsClass->addfield($tabs,"sendmessageInbox","Inbox");
            $functionsClass->addfield($tabs,"sendmessageSent","Sent");
    		return $this->render('profilesBundle:Messages:index.html.twig',array("id" => $otakusClass->getId(),"tabs" => $tabs));
    	}
    	return new Response("");
    }
    public function sendMessageAction()
    {
    	$otakusClass = new otakusClass($this);
    	if ($otakusClass->isRole("USER"))
        {
	        $request = Request::createFromGlobals();
        	$request->getPathInfo();
            $id = $request->query->get("id");
            $messageid = $request->query->get("messageid");
            $title = "";
            $message = "";
            $repository = $this->getDoctrine()->getManager()->getRepository('classesclassBundle:otakus');
            $otaku = $repository->findOneBy(array("id" => $id));
            $repository = $this->getDoctrine()->getManager()->getRepository('classesclassBundle:privateMessages');
            $message = $repository->findOneBy(array("id" => $messageid));
            if ($message != null)
            {
                $title = $message->title;
                $message = $message->message;
            }
    		return $this->render('profilesBundle:Messages:sendmessage.html.twig',array("id" => $id,"title" => $title,"message" => $message,"user" => $otaku));
    	}
    	return new Response("");
    }

    public function sendMessagesSavedAction()
    {
    	$otakusClass = new otakusClass($this);
    	$functionsClass = new functionsClass($this);
    	if ($otakusClass->isRole("USER"))
        {
        	$privateMessages = new privateMessages();
        	$functionsClass->copyRequestObject($privateMessages);
        	$privateMessages->sendotakuid = $otakusClass->getId();
        	
        	$em = $this->getDoctrine()->getManager();
        	$em->persist($privateMessages);
        	$em->flush();
        }
        return new Response("");
    }
}