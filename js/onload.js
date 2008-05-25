function OnAdminSearchChange () {
   Debug(this.value);
   $('ajaxCurrentSearch').value = this.value;
   $('ajaxCurrentSelection').value = ",";
   sigTwo.setRequestParams("search=" + $('ajaxCurrentSearch').value);
   sigTwo.resetContents();
   sigTwo.requestContentRefresh(0);
}

function onDirectoryScroll ( liveGrid, offset ) {
   $('ajaxCurrentOffset').value = offset;
   Debug('onDirectoryScroll(liveGrid, ' + offset + ')');
   setTimeout(Apply, 1000);
}

function onEntryClick () {
   if (this.checked) {
      Debug("Selected Entry # " + this.value);
      $('ajaxCurrentSelection').value += this.value + ',';
   } else {
      Debug("De-Selected Entry # " + this.value);
      var re = new RegExp(',' + this.value + ',');
      $('ajaxCurrentSelection').value = $('ajaxCurrentSelection').value.replace(re, ",");
   }

   var oto = $('ajaxCurrentOffset').value;
   sigTwo.setRequestParams("query=" + $('ajaxCurrentSelection').value);
   sigTwo.resetContents();
   sigTwo.scroller.moveScroll(oto);
   sigTwo.scroller.handleScroll();
   setTimeout(Apply, 1000);
}

function listingList_onUpdate () {
   var i;
   for (i=0; i<$('listingList').childNodes.length; i++) {
      $('listingList').childNodes[i].childNodes[1].value=(i+1);
   }
}

function bodyOnLoad () {
   //$('ajaxEnabled').value = "true";
   //$('ajaxCurrentSelection').value = ",";

   var myrules = {
      '#searchInput' : function (el) {
         el.onkeyup = OnAdminSearchChange;
      },

      'input.listCheckbox' : function (el) {
         el.onclick = onEntryClick; 
      }
   };

   //Behaviour.register(myrules);

   //sigOne = new Rico.Accordion( ('accordionDiv'), {panelHeight:300} );

   //if (sigCurrentTabId) {
   //   sigOne.showTabByIndex(sigCurrentTabId);
   //}

   //var opts = { prefetchBuffer: true, onscroll: onDirectoryScroll };
   //sigTwo = new Rico.LiveGrid ('directory', 5, 1000, ajaxCallback, opts);

   //sigThree = new Effect.Round('div', 'rounded', {border: 'black'});

   //setTimeout(Apply, 1000);
   Sortable.create('listingList', {only: 'orderable', onUpdate: listingList_onUpdate});
}

function Apply () {
   Debug('Behaviour.apply();');
   Behaviour.apply();
}

function Debug (message) {
   $('debug').innerHTML += message + "<br/>";
}
