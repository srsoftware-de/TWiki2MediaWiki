button=null;
pause=100;
revision=document.getElementById('first_revision');

function clickButton(){
  if (button!=null){
    button.click();
  }
}

if (revision!=null){
  plainsubmit=document.getElementById('plain-submit');
  if (plainsubmit!=null){
    button=plainsubmit;
  } else {
//    pause=10000;
    button=revision;
  }
} else {
  todosubmit=document.getElementById('todo-submit');
  if (todosubmit!=null){
    button=todosubmit;
  } else {
    page=document.getElementById('first_open_page');
    if (page!=null){
      button=page;
      pause=5000;
    }
  }
}
setTimeout("clickButton()",pause);
