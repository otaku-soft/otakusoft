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
                $theuser->$key = $value;// if valid copy
                
            }
            $em->flush();

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
     public function searchDatatablesVariable()
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
        $connection = $this->get('doctrine.dbal.default_connection');
        $joinstring = "";
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
        $queryCountString= 'SELECT COUNT(*)
        FROM '.$table.' a '.$searchstring;
        if (count($types) > 0)
        {
            if ( isset($request['search']) && $request['search']['value'] != '' )
            {
                if (trim($searchstring) != "")
                $searchstring = $searchstring. " AND ";

                $searchstring = $searchstring." ( ";

                $searchcounter = 0;
                foreach ($types as $type)
                {
                    if ($searchcounter != 0)
                    $searchstring = $searchstring." OR ";

                    $searchstring = $searchstring." ".$type. "  LIKE '%".$request['search']['value']."%'";

                    $searchcounter++;

                }

                $searchstring = $searchstring." ) ";
            }
        }
        if ($searchstring != "" && $wherecounter == 0)
        {
            $searchstring = "WHERE ".$searchstring;
            $wherecounter++;
        }
        if ( isset($request['order']) && count($request['order']) ) 
        {

            $orderby = $this->order($request,$types);
        }
        $afterSelect = "";
        $this->customquery($customquery,$joinstring,$searchstring,$afterSelect);
        $distinctField = 'a.id';
        $queryFilteredCountString= 'SELECT COUNT(DISTINCT('.$distinctField.'))
        FROM  '.$table.' a   '.$joinstring.' '.$searchstring.' '.$orderby;
        $queryString = 'SELECT * '.$afterSelect.'
        FROM '.$table.' a  '.$joinstring.'  '.$searchstring.' '.$orderby.' LIMIT '.$start.','.$maxresults;
        
        
        $recordsTotalData = $connection->executeQuery($queryCountString)->fetchAll();
        $recordsTotal = $recordsTotalData[0]['COUNT(*)'];
        $recordsFilteredData = $connection->executeQuery($queryFilteredCountString)->fetchAll();

        $recordsFiltered = $recordsFilteredData[0]['COUNT(DISTINCT('.$distinctField.'))'];
        $items = $connection->executeQuery($queryString)->fetchAll();
        $jsonheader = '{
        
        "recordsTotal": '.$recordsTotal.',
        "recordsFiltered": '.$recordsFiltered.',
        "data": [';
        $generalMethods = $this->get("functionsClass");
        $jsoncontent = $this->render($template, array('items' => $items,'request' => $request))->getContent();
        $generalMethods->datatablesFilterJson($jsoncontent);
        $jsonfooter = ']
        }';
        
        return $jsonheader.$jsoncontent.$jsonfooter; 
        
        return new Response("")  ;
     }

    public function customquery($customquery,&$joinstring,&$searchstring,&$afterSelect)
    {
       if ($customquery == "friendlist")
        {
            $joinstring = " LEFT JOIN otakus  on otakus.id = a.otakuid ";
        }
        if ($customquery == "friendlistview")
        {
            $joinstring = " LEFT JOIN otakus  on otakus.id = a.friendotakuid ";
        }
        if ($customquery == "sentmessages")
        {
            $joinstring = "LEFT JOIN otakus on otakus.id = a.tootakuid";
        }

        if ($customquery == "inboxmessages")
        {
            $joinstring = "RIGHT JOIN otakus on otakus.id = a.sendotakuid";
            $afterSelect = ",a.id as messageid";
        }

    }
    public function searchDatatablesAction()
    {
        return new Response($this->searchDatatablesVariable());
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
    public function searchTemplateAction($options)
    {
        return    $this->render($options->searchtemplate, array("options" => $options));
    }
    public function authfailuremssage()
    {
        return "not authorized";
    }
}