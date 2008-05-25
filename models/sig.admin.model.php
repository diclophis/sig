<?

$SiG_Tables = array('node_data', 'node_rel', 'property_data', 'property_types');

class SiG_Admin_Model extends SiG_Model {

   function SiG_Admin_Model ()
   {
      //SiG_Admin_Model Constructor
   }

   function doDefault ($container)
   {
      if (SiG_Session::Instance()->UserHavePerm()) {
         $tableFieldset = new Tag('fieldset');
         $tableLegend = new Tag('legend');
         $tableLegend->AddElement('Database Table Management');
         $tableFieldset->AddElement($tableLegend);
         $p = new Tag('p');
         $tableButtons = new Tag('div');
         if (SiG_Admin_Model::TablesExist()) {
            $p->AddElement('Database Tables Exist');
            $dropButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Drop Database Tables'));
            $tableButtons->AddElement($dropButton);
         } else {
            $p->SetAttribute('class', 'sig_notice_error');
            $p->AddElement('Tables Do Not Exist');
            $installButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Install Database Tables'));
            $tableButtons->AddElement($installButton);
         }
         $tableFieldset->AddElement($p);
         $tableFieldset->AddElement($tableButtons);
         $container->AddElement($tableFieldset);
         $i = 0;
         if (SiG_Admin_Model::TablesExist()) {
         /*
            $templateFieldset = new Tag('fieldset'); 
            $templateLegend = new Tag('legend');
            $templateLegend->AddElement('Template File');
            $templateButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Install Template'));
            $templateFieldset->AddElement($templateLegend);
            $templateFieldset->AddElement($templateButton);
            $container->AddElement($templateFieldset);
         */
            $featuresFieldset = new Tag('fieldset');
            $featuresLegend = new Tag('legend');
            $featuresLegend->AddElement('Features');
            $featuresFieldset->AddElement($featuresLegend);
            $featuresListing = new Tag('ul');
            foreach (Node::get_array_of_installed_modules() as $title=>$class) {
               if (strpos($class, 'System') !== FALSE) {
                  $system = Node::GetSystemNodeByTitle($title);
                  $li = new Tag('li');
                  $activeInput = new Tag('input', array('type'=>'checkbox', 'name'=>'classes['.$i.']', 'value'=>$class));
                  $titleInput = new Tag('input', array('type'=>'text', 'name'=>'titles['.$i.']', 'value'=>$title));
                  if ($system != NULL) {
                     $activeInput->SetAttribute('checked','checked');
                  }
                   
                  $li->AddElement($activeInput);
                  $li->AddElement($titleInput);
                  $featuresListing->AddElement($li);
                  $i++;
               }
            }

            $featuresFieldset->AddElement($featuresListing);
            $featuresButtonsDiv = new Tag('div');
            $featuresButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Activate Features'));
            $featuresButtonsDiv->AddElement($featuresButton);
            $featuresFieldset->AddElement($featuresButtonsDiv);
            $container->AddElement($featuresFieldset);
         }
      }
   }

   function doDrop_Database_Tables ($container)
   {
      $p = new Tag('p', array('class'=>'sig_notice_warning'));
      $p->AddElement('This action is irreversable!');
      $container->AddElement($p);
      $confirmButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Confirm Drop Database Tables'));
      $container->AddElement($confirmButton);
   }

   function doConfirm_Drop_Database_Tables ($container)
   {
      global $SiG_Tables;

      foreach ($SiG_Tables as $table) {
         $query = 'DROP TABLE '.SiG_Controller::GetTablePrefix().$table;
         $result = new Query($query);
      }

      $messageFieldset = new Tag('fieldset');
      $messageLegend = new Tag('legend');
      $messageLegend->AddElement('Message');
      $messageFieldset->AddElement($messageLegend);
      $p = new Tag('p', array('class'=>'sig_notice_ok'));
      $p->AddElement('Tables Dropped');

      $messageFieldset->AddElement($p);

      $container->AddElement($messageFieldset);

      $this->doDefault($container);
   }

//TODO fix this
   function doInstall_Database_Tables ($container)
   {
      //require_once(ABSPATH.'wp-admin/upgrade-functions.php');

      $creates = sprintf(file_get_contents(SiG_Controller::BasePath().'/include/SiG.sql'),
             SiG_Controller::GetTablePrefix(),
             SiG_Controller::GetTablePrefix(),
             SiG_Controller::GetTablePrefix());
//echo $sql;
//die();
      $queries = explode(";", $creates);
      foreach ($queries as $sql) {
         $query = new Query($sql);
         $query->execute();
      }

      if (TRUE) {
         $messageFieldset = new Tag('fieldset');
         $messageLegend = new Tag('legend');
         $messageLegend->AddElement('Message');
         $messageFieldset->AddElement($messageLegend);
         $p = new Tag('p', array('class'=>'sig_notice_ok'));
         $p->AddElement('Tables Created');

         $messageFieldset->AddElement($p);

         $container->AddElement($messageFieldset);

         $this->doDefault($container);
      }
   }

   function doActivate_Features ($container)
   {
      $classes = SiG_Session::Instance()->Request('classes');
      $titles = SiG_Session::Instance()->Request('titles');

      foreach ($classes as $orderby=>$class) {
         $system = Node::GetSystemNodeByTitle($titles[$orderby]);
         if ($system == NULL) {
            $this->CreateNode($class, $titles[$orderby], 0, $orderby);
         }
      } 

      $messageFieldset = new Tag('fieldset');
      $messageLegend = new Tag('legend');
      $messageLegend->AddElement('Message');
      $messageFieldset->AddElement($messageLegend);
      $p = new Tag('p', array('class'=>'sig_notice_ok'));
      $p->AddElement('Features Activated');

      $messageFieldset->AddElement($p);

      $container->AddElement($messageFieldset);

      $this->doDefault($container);
   }

   function CreateNode ($nodeType, $title, $parentId, $orderby)
   {
      $nodeValues = array(
         'parent_ids'=>array($orderby=>$parentId)
         //'user_id'=>SiG_Session::Instance()->GetUserData()->ID,
         //'group_id'=>'1'
      );

      $struct = array(
         'node_type'=>$nodeType,
         'title'=>$title
      );

      $node = call_user_func_array(array($nodeType, 'create'), array($nodeValues, $nodeType));
      $node->update($nodeValues, $struct);
      return $node;
   }

   function doReInitialize_Database ($container)
   {
      $this->doInstall_Database_Tables($container);
   }

//TODO fix this
   function TablesExist ()
   {
      global $SiG_Tables;

      //$tables = array('node_data', 'property_data', 'property_types');
      $exist = TRUE;
      foreach ($SiG_Tables as $table) {
         $sql = 
                "show tables like '"
                . SiG_Controller::GetTablePrefix()
                . $table
                . "'"
                ;
         $query = new Query($sql);
         $result = $query->execute();
         if (!is_object($result)) {
            $exist = FALSE;
         }
         //$var = ($query->execute('array'));
         //if ($var[0] != SiG_Controller::GetTablePrefix().$table) {
         //   $exist = FALSE;
         //}
      }

      return $exist;
   }
/*
   function doInstall_Template ($container)
   {
      $messageFieldset = new Tag('fieldset');
      $messageLegend = new Tag('legend');
      $messageLegend->AddElement('Message');
      $messageFieldset->AddElement($messageLegend);
      $p = new Tag('p');
      if (copy(SiG_Controller::BasePath().'/templates/sig-page-template.php', TEMPLATEPATH.'/sig-page-template.php')) {
         $p->SetAttribute('class', 'sig_notice_ok');
         $p->AddElement('sig-page-template installed');
      } else {
         $p->SetAttribute('class', 'sig_notice_error');
         $p->AddElement('Unable to install sig-page-template.php');
      }
      $messageFieldset->AddElement($p);
      $container->AddElement($messageFieldset);

      $this->doDefault($container);
   }
*/
}

?>
