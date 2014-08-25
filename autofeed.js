page=document.getElementById('first_open_page');

function clickPage(){
  page.click();
}

if (page!=null){
  setTimeout("clickPage()",1000);
}
