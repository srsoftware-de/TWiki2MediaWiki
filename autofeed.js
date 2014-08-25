button=null;
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
    }
  }
}
alert(button.value);
setTimeout("clickButton()",1000);
