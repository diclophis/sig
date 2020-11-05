<?php

class BusinessDirectoryEntries_Node extends System_Node {
   var $struct = array(
                    "node_type" => "BusinessDirectoryEntries_Node",
                    
                    "title" => array(
                                  "prop_type" => "text",
                                  "values" => "user should never see edit for this"
                               )
                 );

   var $allowed_children_types = array(
                                    "Business Entry" => "BusinessDirectoryEntry_Node"
                                 );

   function ListingFieldsetElement ()
   {
         $listingFieldset = new Tag('fieldset');
         $listingLegend = new Tag('legend');
         $listingLegend->AddElement('Listing');
         $listingFieldset->AddElement($listingLegend);
         $listingList = new Tag('ul', array('id'=>'listingList'));
         foreach ($this->get_array_of_children('business_name', 'ASC') as $child) {
            SiG_Session::Dispatch($listingList, $child, 'view', 'Listing', 'view');
         }
         $listingFieldset->AddElement($listingList);
         return $listingFieldset;
   }

   function BrowseInfoElement ()
   {
      $p = new Tag('div');
      $p->AddElement('Entries');
      return $p;
   }
}

?>
