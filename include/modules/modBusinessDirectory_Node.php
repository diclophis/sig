<?

class BusinessDirectory_Node extends Node {
   var $struct = array(
                    "node_type" => "BusinessDirectory_Node",
                    
                    "title" => array(
                                  "prop_type" => "text",
                                  "values" => "New Business Directory"
                               )
                 );

   var $allowed_children_types = array(
                                    "Business Categories", "BusinessDirectoryCategories_Node",
                                    "Business Entries" => "BusinessDirectoryEntries_Node"
                                 );

   function create ($nodeValues, $returnType)
   {
      $node = parent::create($nodeValues, $returnType);
      SiG_Admin_Model::CreateNode('BusinessDirectoryCategories_Node', 'Business Categories', $node->id, 0);
      SiG_Admin_Model::CreateNode('BusinessDirectoryEntries_Node', 'Business Entries', $node->id, 1);
      return $node;
   }

   function SubmitFieldsetElement ()
   {
      return NULL;
   } 

   function DefaultHtmlData ($container, $parentNode = NULL, $activeNode = NULL)
   {
      $div = new Tag('div');

      $status = SiG_Session::Instance()->Request('status');

      switch ($status) {
         case 'Search':

            $categories_to_search = SiG_Session::Instance()->Request('categories_to_search');

            $matches = array();
            if ($categories_to_search) {
               foreach ($categories_to_search as $categoryId) {
                  $category = Node::new_instance($categoryId);
                  $parents = $category->GetParentIds();
                  array_shift($parents);
                  $matches = array_merge($parents, $matches);
               }
            }

            $matches = array_unique($matches); 

            foreach ($matches as $nodeId) {
               $node = Node::new_instance($nodeId);
               print_r($node);
            }
         break;

         default:
      $form = new Tag('form', array('action'=>SiG_Plugin_Controller::Permalink(), 'method'=>'post'));
      $selectElement = new Tag('select', array('name'=>'categories_to_search[]', 'multiple'=>'multiple', 'size'=>'10'));
      $this->get_child(0)->RecursiveOptionElement(array(), array(), $selectElement, 0,
                            array('BusinessDirectoryEntries_Node'), TRUE);

                                           //array());
      //$attachButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Attach'));
      //$detachButton = new Tag('input', array('type'=>'submit', 'name'=>'action', 'value'=>'Detach'));

      $searchButton = new Tag('input', array('type'=>'submit', 'name'=>'status', 'value'=>'Search'));

      $form->AddElement($selectElement);
      $form->AddElement($searchButton);

      $div->AddElement($form);
         break;
      }
 
      $container->AddElement($div);

   }
}

?>
