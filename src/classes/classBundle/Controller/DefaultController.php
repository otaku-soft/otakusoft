<?php

namespace classes\classBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

error_reporting(0);
class DefaultController extends Controller
{
    public $searchQuery;
    public $searchQueryCount;
    public function updateAction()//generic update method
    {
        try
        {
            $request = Request::createFromGlobals();
            // the URI being requested (e.g. /about) minus any query parameters
            $request->getPathInfo();
            $table = $request->request->get('table','');
            $findby = $request->request->get('findby','');

            $arr =  $request->request->all();
            $session = $this->getRequest()->getSession();
            $findbyid = $request->request->get($findby);

            $em = $this->getDoctrine()->getManager();
             $repository = $em->getRepository('classesclassBundle:'.$table);
            //find if user name and password exists in members table
            $theuser = $repository->findOneBy(array($findby => $findbyid));

                while (list($key, $value) = each($arr))
                {
                    if ($key != "table" && $key != "findby")
                    {
                        $valid = true;
                        if ($table == "accountsUsers" && $key == "email")//exception 1 - users email
                        {
                            $checkForExistingEmail = $repository->findOneBy(array("email" => $value));
                            if ($checkForExistingEmail != null)
                            $valid  = false;
                        }
                        if ($valid)
                        $theuser->$key = $value;// if valid copy
                    }
                }
            $em->flush();

            //return $this->redirect($this->generateUrl('_account_index'));
               return new Response("saved");

           }

        catch (Exception $e)
          {
              return new Response("Error occured!: ".$e->getMessage());

          }

  }



    public function addAction()//almost generic add method
    {

        $request = Request::createFromGlobals();
        // the URI being requested (e.g. /about) minus any query parameters
        $request->getPathInfo();
        $table = $request->request->get('table','');
        $em = $this->getDoctrine()->getManager();

        $arr =  $request->request->all();


        while (list($key, $value) = each($arr))
        {

            if ($key != "table" && $key != "findby")
            $item->$key = $value;
        }

        $em->persist($item);
        $em->flush();

        return new Response($item->id);


    }


    public function deleteAction()//generic delete method
    {

        try
        {
            $request = Request::createFromGlobals();
            // the URI being requested (e.g. /about) minus any query parameters
            $request->getPathInfo();
            $table = $request->request->get('table','');
            $findby = $request->request->get('findby','');

            $arr =  $request->request->all();
            $session = $this->getRequest()->getSession();
            $findbyid = $request->request->get($findby);

            $em = $this->getDoctrine()->getManager();
             $repository = $em->getRepository('classesclassBundle:'.$table);

            $theuser = $repository->findOneBy(array($findby => $findbyid));
            $deletedid = $theuser->id;

            $em->remove($theuser);
            $em->flush();

            return new Response('object deleted id '.$deletedid);

        }
        catch (Exception $e)
        {
            return new Response("Error occured!: ".$e->getMessage());

        }


    }
    function proper_escape($datastring) {
         # Strip slashes if data has already been escaped by magic quotes
         if(get_magic_quotes_gpc()):
             $datastring = stripslashes($datastring);
         endif;

         return $datastring;
     }
     public function filterprofilevar(&$variable)
     {
         foreach ($variable as &$value)
         {
             $value = str_replace("%20","",$value);
            $value = str_replace("\n","",$value);
            $value = str_replace(",","¸",$value);
         }

     }
     public function searchCsvAction()
     {
         $pagenumber = 1;
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $pagenumber = $request->request->get('csvpagenumber',1);
        $pagetype = $request->request->get('csvtype',"");
        $this->searchVariables(500,$pagenumber);
        $items = $this->searchQuery->getResult();
        $profileClass  = new profiles($this);
        if ($pagetype == "participantSelections")
        {

            $csvstring =  'Plan name during creation,Name - First,Name - Last,Date generated,Current Balance,Pre-Tax per paycheck,Catch Up Contributions,After-Tax (Roth) per paycheck,Recommended Monthly Plan Contribution,contributionCurrent,rothContributionCurrent,Investment Type,Portfolio Name';
            $csvstring =  $csvstring."\n";

            foreach ($items as &$item)
            {
                $profile = array();
                $profile = $profileClass->returnProfile($item->id);
                $this->filterprofilevar($profile);
                $csvstring = $csvstring.$profile['planName'].','.$profile['participant']['firstName'].','.$profile['participant']['lastName'].','.$profile['reportDate'].','.$profile['currentBalance'].','.$profile['contributions']['pre'].','.$profile['contributions']['catchupContributions'].','.$profile['contributions']['roth'].','.$profile['retirementNeeds']['recommendedMonthlyPlanContribution'].','.$profile['contributions']['preCurrent'].','.$profile['contributions']['rothCurrent'].','.$profile['investmentType'].','.$profile['pname'];
                $csvstring =  $csvstring."\n";
            }

             header("Cache-Control: must-revalidate");
               header("Pragma: must-revalidate");
            header("Content-Description: File Transfer");
            header('Content-Disposition: attachment; filename=participant-selections.csv');
            header("Content-type: application/vnd.ms-excel");
            header('Content-Transfer-Encoding: binary');
            echo $csvstring;
        }
        if ($pagetype == "participantEmails")
        {
            $csvstring = 'id,Planid,Email';
            $csvstring =  $csvstring."\n";
            foreach ($items as &$item)
            {
                $profile = array();
                $profile = $profileClass->returnProfile($item->id);
                if (trim($profile['participant']['email']) != "")
                {
                    $csvstring = $csvstring.$profile['id'].','.$profile['planid'].','.$profile['participant']['email'];
                    $csvstring =  $csvstring."\n";
                }
            }

             header("Cache-Control: must-revalidate");
               header("Pragma: must-revalidate");
            header("Content-Description: File Transfer");
            header('Content-Disposition: attachment; filename=participant-emails.csv');
            header("Content-type: application/vnd.ms-excel");
            header('Content-Transfer-Encoding: binary');
            echo $csvstring;
        }
        if ($pagetype == "participantBeneficiaries")
        {
            $csvstring = 'Plan name during creation,Participant Name - First,Participant Name - Last,Date generated, Beneficiary Type, Beneficiary First Name, Beneficiary Last Name, Beneficiary Gender, Beneficiary Relationship, Beneficiary Martial Status, Beneficiary Social Security Number, Beneficiary Percent';
            $csvstring =  $csvstring."\n";
            foreach ($items as &$item)
            {
                $profile = array();
                $profile = $profileClass->returnProfile($item->id);
                $this->filterprofilevar($profile);
                $this->filterprofilevar($profile['beneficiaries']);
                foreach ($profile['beneficiaries'] as $beneficiary)
                {
                    $csvstring = $csvstring.$profile['planName'].','.$profile['participant']['firstName'].','.$profile['participant']['lastName'].','.$profile['reportDate'].','.$beneficiary['type'].','.$beneficiary['firstName'].','.$beneficiary['lastName'].','.$beneficiary['firstName'].','.$beneficiary['gender'].','.$beneficiary['maritalStatus'].','.$beneficiary['ssn'].','.$beneficiary['percent'];
                    $csvstring =  $csvstring."\n";
                }
            }

             header("Cache-Control: must-revalidate");
               header("Pragma: must-revalidate");
            header("Content-Description: File Transfer");
            header('Content-Disposition: attachment; filename=participant-beneficiaries.csv');
            header("Content-type: application/vnd.ms-excel");
            header('Content-Transfer-Encoding: binary');
            echo $csvstring;
        }

        return new Response("");

     }
     public function searchCsvCountAction()
     {
         $this->searchVariables(1);
         return new Response($this->searchQueryCount);
     }

     public function searchDatatablesAction()
     {
         $em = $this->getDoctrine()->getManager();
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $request = $request->request->all();
        $table = $request['table'];
        $start  = $request["start"];
        $maxresults = $request["length"];
        $searchstring = "";
        $types = explode(",",$request['types']);
        $template = $request['template'];
        $findby = explode(",",$request['findby']);
        $findbyvalues = explode(",",$request['findbyvalues']);
        $customquery = $request['customquery'];
        $wherecounter = 0;



        if ($request['findby'] != "")
        if (count($findby) == count($findbyvalues))
        {


            $searchstring = $searchstring." ( ";
            for ($i = 0; $i < count($findby); $i++)
            {
                if ($i > 0)
                {
                    if (substr_count($findby[$i],"^") > 0)
                    $searchstring = $searchstring." AND ";
                    if (substr_count($findby[$i],"|") > 0)
                    $searchstring = $searchstring." OR ";

                    $findby[$i] = substr($findby[$i],1,strlen($findby[$i]));
                }
                $searchstring = $searchstring.$findby[$i]." = '".$findbyvalues[$i]."'";
            }

            $searchstring = $searchstring." ) ";

        }

        if ($searchstring != "" && $wherecounter == 0)
        {
            $searchstring = "WHERE ".$searchstring;
            $wherecounter++;
        }
        if ($customquery ==  "adviserPlanList")
        {
            $queryCountString= 'SELECT COUNT(a) FROM classesclassBundle:'.$table.' a
            JOIN a.plan b  '.$searchstring;
        }
        else
        {
            $queryCountString= 'SELECT COUNT(a)
            FROM classesclassBundle:'.$table.' a '.$searchstring;
        }


        if (count($types) > 0)
        {
            if ( isset($request['search']) && $request['search']['value'] != '' )
            {

                $searchstring = $searchstring. " AND ";

                $searchstring = $searchstring." ( ";

                $searchcounter = 0;
                foreach ($types as $type)
                {
                    if ($searchcounter != 0)
                    $searchstring = $searchstring." OR ";
                    $searchstring = $searchstring." ".$type. "  LIKE :searchvalue";
                    $searchcounter++;

                }

                $searchstring = $searchstring." ) ";
            }


            if ( isset($request['order']) && count($request['order']) )
            {

                //$orderby = $this->order($request,$types);
                //$searchstring = $searchstring." ".$orderby;
            }
        }

        if ($customquery == "profileList")
        {
            $beforefiltersearchstring  = $searchstring;
            if ($searchstring == "")
            {
                $searchstring = "WHERE profiles.id > 0";
                $wherecounter++;
            }

            $profilesClassVar = new profiles($this);
            $profileSections = $profilesClassVar->sections();
            $joinString = "JOIN profiles.participant  AS participant";
            $checkArray = array();
            $request = Request::createFromGlobals();
            $request->getPathInfo();

            foreach ($profileSections as $section)
            {

                $searchstringcompare = $searchstring;
                $operationsvalue = str_replace("PLUS"," + ",$section->name);
                $operationsvalue =  str_replace("_",".",$operationsvalue);
                $operationsvalue = $section->database.'.'.$operationsvalue;

                 if ($section->type == "number")
                 {


                     if (!is_numeric($request->request->get("equalto_".$section->name."_".$section->database)))
                     {
                         if (is_numeric($request->request->get("lessthan_".$section->name."_".$section->database)))
                         {
                             $comparevalue = floatval($request->request->get("lessthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  '.$operationsvalue.' < '.$comparevalue;
                         }
                         if (is_numeric($request->request->get("greaterthan_".$section->name."_".$section->database)))
                         {
                             $comparevalue = floatval($request->request->get("greaterthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  '.$operationsvalue.' > '.$comparevalue;
                         }
                     }
                     else
                     {
                         if (!is_numeric($request->request->get("lessthan_".$section->name."_".$section->database)) && !is_numeric($request->request->get("greaterthan_".$section->name."_".$section->database)))
                         {
                             $comparevalue = floatval($request->request->get("equalto_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  '.$operationsvalue.' = '.$comparevalue;
                         }
                         else if (is_numeric($request->request->get("lessthan_".$section->name."_".$section->database)) && !is_numeric($request->request->get("greaterthan_".$section->name."_".$section->database)))
                         {
                             $comparevalue = floatval($request->request->get("equalto_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  ( '.$operationsvalue.' = '.$comparevalue;
                             $comparevalue = floatval($request->request->get("lessthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' OR  '.$operationsvalue.' < '.$comparevalue.' )';

                         }
                         else if (!is_numeric($request->request->get("lessthan_".$section->name."_".$section->database)) && is_numeric($request->request->get("greaterthan_".$section->name."_".$section->database)))
                         {
                             $comparevalue = floatval($request->request->get("equalto_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  ( '.$operationsvalue.' = '.$comparevalue;
                             $comparevalue = floatval($request->request->get("greaterthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' OR  '.$operationsvalue.' > '.$comparevalue.' )';
                         }
                         else
                         {

                             $comparevalue = floatval($request->request->get("lessthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  ( ( '.$operationsvalue.' < '.$comparevalue;
                             $comparevalue = floatval($request->request->get("greaterthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND   '.$operationsvalue.' > '.$comparevalue.' ) ';
                              $comparevalue = floatval($request->request->get("equalto_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' OR   '.$operationsvalue.' = '.$comparevalue.' ) ';
                         }
                     }
                 }
                if ($section->type == "checkboxes")
                {

                    if (trim($request->request->get("checkboxes_".$section->name."_".$section->database)) != "")
                    {
                     $checkBoxes = $this->proper_escape($request->request->get("checkboxes_".$section->name."_".$section->database));
                     $checkValues = explode(",",$checkBoxes);
                     $checkstring = "";
                     foreach ($checkValues as $checkValue)
                     {
                         $currentKey = $section->database.'_'.$section->name.'_'.str_replace(" ","_",$checkValue);
                         if ($checkstring == "")
                         $checkstring = $operationsvalue.' LIKE :'.$currentKey;
                         else
                         $checkstring = $checkstring.' OR '.$operationsvalue.' LIKE :'.$currentKey;
                         $checkArray[$currentKey] = $checkValue;

                     }
                     $checkstring = " AND (".$checkstring.")";
                     $searchstring = $searchstring.$checkstring;
                    }

                }

                if ($searchstringcompare != $searchstring && $section->database != "profiles" && substr_count($joinString,$section->database) < 1)
                $joinString = $joinString." JOIN  profiles.".$section->database." ".$section->database;

            }
            $request = $request->request->all();
        }
            //echo $searchstring;


        if ($searchstring != "" && $wherecounter == 0)
        {
            $searchstring = "WHERE ".$searchstring;
            $wherecounter++;
        }

        if ( isset($request['order']) && count($request['order']) )
        {

            $orderby = $this->order($request,$types);
            $searchstring = $searchstring." ".$orderby;
        }
        if ($customquery ==  "adviserPlanList")
        {
            $queryFilteredCountString= 'SELECT COUNT(a) FROM classesclassBundle:'.$table.' a
            JOIN a.plan b '.$searchstring;

            $queryString = 'SELECT a, b FROM classesclassBundle:'.$table.' a
            JOIN a.plan b   '.$searchstring;

        }
        else if ($customquery == "profileList")
        {

             $queryFilteredCountString= 'SELECT COUNT(DISTINCT profiles)
            FROM classesclassBundle:'.$table.' profiles '.$joinString.' '.$beforefiltersearchstring;
            $queryCountString= 'SELECT COUNT(DISTINCT profiles)
            FROM classesclassBundle:'.$table.' profiles '.$joinString.' '.$searchstring;
            $queryString = 'SELECT profiles
            FROM classesclassBundle:'.$table.' profiles '.$joinString.' '.$searchstring;

        }
        else
        {

            $queryFilteredCountString= 'SELECT COUNT(a)
            FROM classesclassBundle:'.$table.' a '.$searchstring;
            $queryString = 'SELECT a
            FROM classesclassBundle:'.$table.' a '.$searchstring;



        }

        $query = $em->createQuery
        (
            $queryCountString
        );



        $recordsTotal = $query->getSingleScalarResult();

        $query = $em->createQuery
        (
            $queryFilteredCountString
        );

        if ( isset($request['search']) && $request['search']['value'] != '' )
        $query->setParameter('searchvalue', '%'.$request['search']['value'].'%');
        $recordsFiltered = $query->getSingleScalarResult();


        $query = $em->createQuery
        (
            $queryString
        );

        if ( isset($request['search']) && $request['search']['value'] != '' )
        $query->setParameter('searchvalue', '%'.$request['search']['value'].'%');
        $query->setMaxResults($maxresults);
        $query->setFirstResult($start);
        $items = $query->getResult();


        $jsonheader = '{

        "recordsTotal": '.$recordsTotal.',
        "recordsFiltered": '.$recordsFiltered.',
        "data": [';

        $jsoncontent = $this->render($template, array('items' => $items));

        $jsoncontent = $jsoncontent->getContent();
        $jsoncontent = str_replace(" ","^//blankspace//^",$jsoncontent);
        $jsoncontent = filter_var($jsoncontent, FILTER_SANITIZE_URL);
        $jsoncontent = str_replace("^//blankspace//^"," ",$jsoncontent);
        $jsonfooter = ']
        }';

         return new Response($jsonheader.$jsoncontent.$jsonfooter);

     }
    public  function order ( $request, $columns )
    {
        $order = '';

        if ( isset($request['order']) && count($request['order']) ) {
            $orderBy = array();
            $dtColumns = $this->pluck( $columns, 'dt' );

            for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
                // Convert the column index into the column data property
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];

                $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $columns[ $columnIdx ];

                if ( $requestColumn['orderable'] == 'true' ) {
                    $dir = $request['order'][$i]['dir'] === 'asc' ?
                        'ASC' :
                        'DESC';

                    $orderBy[] = ''.$columns[$requestColumn['data']].' '.$dir;
                }
            }

            $order = 'ORDER BY '.implode(', ', $orderBy);
        }

        return $order;
    }
    public  function pluck ( $a, $prop )
    {
        $out = array();

        for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
            $out[] = $a[$i][$prop];
        }

        return $out;
    }




     public function searchVariables($maxresults = null,$pagenumber = null)
     {

        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $em = $this->getDoctrine()->getManager();
        //post requests

        $table = $request->request->get('table','');

        $findby = $request->request->get('findby','');
        $findbyvalue = $request->request->get('findbyvalue','');

        if ($findby != "false")
        {
            $findbys = explode(",",$findby);
            $findbysvalue = explode(",",$findbyvalue);
            $findbyVars = array();
            foreach ($findbys as $value)
            {
                $findbyVar= $value;
                $valueArray = explode(".",$value);
                if (count($valueArray) > 1)
                $findbyVar= $valueArray[1];
                $findbyVars[] = $findbyVar;
            }
        }

        $types = $request->request->get('types','');
        $template = $request->request->get('template','');
        $value = urldecode($request->request->get('value',''));
        if ($pagenumber == null)
        $pagenumber = $request->request->get('pagenumber','');
        if ($maxresults == null)
        $maxresults = $request->request->get('maxresults','');
        $selectedletter = $request->request->get('selectedletter','');
        $searchtype = $request->request->get('searchtype','');
        $queryReference = $request->request->get("query",'');
        if ($queryReference == "")
        $searchField = "a.";
        else
        $searchField = "";


        if ($searchtype != "")
        $searchtypestring = "ORDER BY ".$searchField.$searchtype;



        $searchstring = "";
        $types = explode(",",$types);

        for ($i = 0; $i < count($types); $i++)
        if ($i == 0)
        $searchstring = '( '.$searchField.$types[$i].' LIKE :searchvalue';
        else
        $searchstring = $searchstring.' OR '.$searchField.$types[$i].' LIKE :searchvalue';
        $searchstring = $searchstring.' ) ';


        if ($selectedletter != "")
        {
            for ($i = 0; $i < count($types); $i++)
            if ($i == 0)
            $searchstring = $searchstring.' AND ( '.$searchField .$types[$i].' LIKE :selectedletter';
            else
            $searchstring = $searchstring.' OR '.$searchField.$types[$i].' LIKE :selectedletter';
            $searchstring = $searchstring.' ) ';
        }

        if ($findby != "false")
        {
            for($i = 0; $i < count($findbys); $i++)
            {
            $searchstring = $searchstring.' AND '.$searchField.$findbys[$i].' = :'.$findbyVars[$i];
            }
            //default search condition that has nothing to do with value searched, ex "id"
        }
        if ($queryReference == "")//one entity
        {
         $queryCountString= 'SELECT COUNT(a)
         FROM classesclassBundle:'.$table.' a
         WHERE '.$searchstring;
         $queryString = 'SELECT a
         FROM classesclassBundle:'.$table.' a
         WHERE '.$searchstring.' '.$searchtypestring;
        }

        if ($queryReference == "adviserPlanList")
        {
            $queryCountString= 'SELECT COUNT(a) FROM classesclassBundle:'.$table.' a
            JOIN a.plan b WHERE '.$searchstring;

             $queryString = 'SELECT a, b FROM classesclassBundle:'.$table.' a
            JOIN a.plan b  WHERE '.$searchstring.' '.$searchtypestring;
        }

        if ($queryReference == "profileList")
        {
             $profilesClassVar = new profiles($this);
             $profileSections = $profilesClassVar->sections();
             $joinString = "JOIN profiles.participant  AS participant";
             $checkArray = array();
             foreach ($profileSections as $section)
             {

                $searchstringcompare = $searchstring;
                $operationsvalue = str_replace("PLUS"," + ",$section->name);
                 $operationsvalue =  str_replace("_",".",$operationsvalue);
                 $operationsvalue = $section->database.'.'.$operationsvalue;
                 if ($section->type == "number")
                 {


                     if (!is_numeric($request->request->get("equalto_".$section->name."_".$section->database)))
                     {
                         if (is_numeric($request->request->get("lessthan_".$section->name."_".$section->database)))
                         {
                             $comparevalue = floatval($request->request->get("lessthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  '.$operationsvalue.' < '.$comparevalue;
                         }
                         if (is_numeric($request->request->get("greaterthan_".$section->name."_".$section->database)))
                         {
                             $comparevalue = floatval($request->request->get("greaterthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  '.$operationsvalue.' > '.$comparevalue;
                         }
                     }
                     else
                     {
                         if (!is_numeric($request->request->get("lessthan_".$section->name."_".$section->database)) && !is_numeric($request->request->get("greaterthan_".$section->name."_".$section->database)))
                         {
                             $comparevalue = floatval($request->request->get("equalto_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  '.$operationsvalue.' = '.$comparevalue;
                         }
                         else if (is_numeric($request->request->get("lessthan_".$section->name."_".$section->database)) && !is_numeric($request->request->get("greaterthan_".$section->name."_".$section->database)))
                         {
                             $comparevalue = floatval($request->request->get("equalto_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  ( '.$operationsvalue.' = '.$comparevalue;
                             $comparevalue = floatval($request->request->get("lessthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' OR  '.$operationsvalue.' < '.$comparevalue.' )';

                         }
                         else if (!is_numeric($request->request->get("lessthan_".$section->name."_".$section->database)) && is_numeric($request->request->get("greaterthan_".$section->name."_".$section->database)))
                         {
                             $comparevalue = floatval($request->request->get("equalto_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  ( '.$operationsvalue.' = '.$comparevalue;
                             $comparevalue = floatval($request->request->get("greaterthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' OR  '.$operationsvalue.' > '.$comparevalue.' )';
                         }
                         else
                         {

                             $comparevalue = floatval($request->request->get("lessthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND  ( ( '.$operationsvalue.' < '.$comparevalue;
                             $comparevalue = floatval($request->request->get("greaterthan_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' AND   '.$operationsvalue.' > '.$comparevalue.' ) ';
                              $comparevalue = floatval($request->request->get("equalto_".$section->name."_".$section->database));
                             $searchstring = $searchstring.' OR   '.$operationsvalue.' = '.$comparevalue.' ) ';
                         }
                     }
                 }
                 if ($section->type == "checkboxes")
                 {

                     if (trim($request->request->get("checkboxes_".$section->name."_".$section->database)) != "")
                     {
                         $checkBoxes = $this->proper_escape($request->request->get("checkboxes_".$section->name."_".$section->database));
                         $checkValues = explode(",",$checkBoxes);
                         $checkstring = "";
                         foreach ($checkValues as $checkValue)
                         {
                             $currentKey = $section->database.'_'.$section->name.'_'.str_replace(" ","_",$checkValue);
                             if ($checkstring == "")
                             $checkstring = $operationsvalue.' LIKE :'.$currentKey;
                             else
                             $checkstring = $checkstring.' OR '.$operationsvalue.' LIKE :'.$currentKey;
                             $checkArray[$currentKey] = $checkValue;

                         }
                         $checkstring = " AND (".$checkstring.")";
                         $searchstring = $searchstring.$checkstring;
                     }

                 }

                 if ($searchstringcompare != $searchstring && $section->database != "profiles" && substr_count($joinString,$section->database) < 1)
                 $joinString = $joinString." JOIN  profiles.".$section->database." ".$section->database;

             }

             $queryCountString= 'SELECT  COUNT( DISTINCT profiles)
             FROM classesclassBundle:'.$table.' profiles '.$joinString.'
             WHERE '.$searchstring;
             $queryString = 'SELECT DISTINCT profiles
             FROM classesclassBundle:'.$table.' profiles '.$joinString.'
             WHERE '.$searchstring.' '.$searchtypestring;


        }


        //other special cases go here
        $query = $em->createQuery
        (
        $queryCountString
        );
        $query->setParameter('searchvalue', '%'.$value.'%');
        if ($findby != "false")
        for($i = 0; $i < count($findbys); $i++)
        $query->setParameter($findbyVars[$i],$findbysvalue[$i]);
        if ($selectedletter != "")
        $query->setParameter('selectedletter', $selectedletter.'%');
        if (isset($checkArray))
        foreach ($checkArray as $checkKey => $checkValue)
        $query->setParameter($checkKey, '%'.$checkValue.'%');

        $count = $query->getSingleScalarResult();

        $query = $em->createQuery
        (
        $queryString
        );
        $query->setParameter('searchvalue', '%'.$value.'%');
        if ($findby != "false")
        for($i = 0; $i < count($findbys); $i++)
        $query->setParameter($findbyVars[$i],$findbysvalue[$i]);
        if ($selectedletter != "")
        $query->setParameter('selectedletter', $selectedletter.'%');

        if (isset($checkArray))
        foreach ($checkArray as $checkKey => $checkValue)
        $query->setParameter($checkKey, '%'.$checkValue.'%');

        $query->setMaxResults($maxresults);
        $query->setFirstResult(($pagenumber-1) * $maxresults);

        $this->searchQuery = $query;
        $this->searchQueryCount = $count;


     }
    public function searchAction()//generic searching
    {
        $request = Request::createFromGlobals();
        $request->getPathInfo();

        $this->searchVariables();
        $items = $this->searchQuery->getResult();
        $page = $request->request->get('template','');
        $responsetemplate = $this->render($page, array('items' => $items));
        if (count($items) > 0)
        $response = new Response($responsetemplate->getContent()."<!--*totalrows*".$this->searchQueryCount."*totalrows*-->");
        else
        $response = new Response("Sorry, no results found<!--*totalrows*".$this->searchQueryCount."*totalrows*-->");
        return $response;
    }


    function searchTemplateAction($options)
    {

        return    $this->render($options->searchtemplate, array("options" => $options));
    }

    public function search($table,$findby, $findbyvalue, $datatype = "items",  $templatename = "fundlist.html.twig", $searchtypes = "name",  $listname = "item",$searchoptionsdisplay = "inline-block", $searchtemplate = "classesclassBundle:Search:search.html.twig",$searchoptionsvaluestring = "", $seachoptionsdescriptionstring = "",$resultsperpage = "",$query = "")
    {
        $item->datatype = $datatype;
        $item->listname = $listname;
        $item->findby = $findby;
        $item->findbyvalue = $findbyvalue;
        $item->templatename = $templatename;
        $item->searchtemplate = $searchtemplate;
        $item->table = $table;
        $item->searchtypes = $searchtypes;
        if ($resultsperpage == "")
        $item->resultsperpage = array(10,20,50,100,200);
        else
        $item->resultsperpage = explode(",",$resultsperpage);

        $item->filterby = $this->alphalist();
        if ($searchoptionsvaluestring== "")
        {
        $item->searchoptions[0]->value = "id DESC";
        $item->searchoptions[0]->description = "Newest-Oldest";
        $item->searchoptions[1]->value = "id ASC";
        $item->searchoptions[1]->description = "Oldest-Newest";
        $item->searchoptions[2]->value = "name ASC";
        $item->searchoptions[2]->description = "A-Z";
        $item->searchoptions[3]->value = "name DESC";
        $item->searchoptions[3]->description = "Z-A";
        }
       else
       {
           $searchoptionsvalues = explode(",",$searchoptionsvaluestring);
           $seachoptionsdescriptions = explode(",",$seachoptionsdescriptionstring);
           for ($i = 0; $i < count($searchoptionsvalues); $i++)
           {
               $item->searchoptions[$i]->value = $searchoptionsvalues[$i];
               if ($seachoptionsdescriptionstring == "")
               $item->searchoptions[$i]->description = $searchoptionsvalues[$i];
               else
               $item->searchoptions[$i]->description = $seachoptionsdescriptions[$i];
           }
       }
       $item->searchoptionsdisplay = $searchoptionsdisplay;
       $item->query = $query;
       //new new table
       return $item;
    }

    private function userid()//returns current userid
    {
        $session = $this->getRequest()->getSession();
        $userid = $session->get("userid");
        return $userid;
    }

    public function authfailuremssage()
    {
        return "not authorized";
    }

    public function alphalist()
    {
        $char = 'a';
        for ($i = 0; $i < 26; $i++)
        $charlist[$i] = $char++;
        return $charlist;
    }

}