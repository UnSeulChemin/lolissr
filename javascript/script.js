

// DARK MODE

const btnDark = document.querySelector(".btn-toggle");
const themeY = document.querySelector("#dark_theme");

btnDark.addEventListener("click", function()
{
  if (themeY.getAttribute("href") == "css/style.css")
  {
    themeY.href = "css/dark_theme.css";
  }

  else
  {
    themeY.href = "css/style.css";
  }
});


const btnCookie = document.querySelector(".btn-toggle");
 

btnCookie.addEventListener("click", function()
{
  document.body.classList.toggle("dark-theme");
  
  let themeX = "light";
  if (document.body.classList.contains("dark-theme"))
  {
    themeX = "dark";
  }
  document.cookie = "theme=" + themeX;
});


// REGISTER CHANGE EYE

y = true;

function changeY()
{
  if (y)
  {
    document.getElementById("pass").setAttribute("type","text");
    document.getElementById("eye").src="images/show.png";
    y = false;
  }

  else
  {
    document.getElementById("pass").setAttribute("type","password");
    document.getElementById("eye").src="images/hide.png";
    y = true;
  }

}


x = true;

function changeX()
{
  if (x)
  {
    document.getElementById("reg_x_password").setAttribute("type","text");
    document.getElementById("eye_x").src="images/show.png";
    x = false;
  }

  else
  {
    document.getElementById("reg_x_password").setAttribute("type","password");
    document.getElementById("eye_x").src="images/hide.png";
    x = true;
  }

}


// LOGIN CHANGE EYE

z = true;

function changeZ()
{
  if (z)
  {
    document.getElementById("login_password").setAttribute("type","text");
    document.getElementById("eye_login").src="images/show.png";
    z = false;
  }

  else
  {
    document.getElementById("login_password").setAttribute("type","password");
    document.getElementById("eye_login").src="images/hide.png";
    z = true;
  }

}


// SETTINGS CHANGE EYE

w = true;

function changeW()
{
  if (w)
  {
    document.getElementById("submit_text_password").setAttribute("type","text");
    document.getElementById("eye_settings").src="images/show.png";
    w = false;
  }

  else
  {
    document.getElementById("submit_text_password").setAttribute("type","password");
    document.getElementById("eye_settings").src="images/hide.png";
    w = true;
  }

}


// AJAX


// DELETE THIS CONTACT

function deleteThisContact(id)
{
  let text = "are you sure you want to delete this contact?";

  if (confirm(text) === true)
  {
    $.ajax({
      type: "GET",
      url: "script/delete.php?id=" + id + "&deleteThisContact=Y"
    }).done(function()
    {
        alert('Contact a été supprimé.');
        window.location.reload();
    });
  }

}


// DELETE ALL CONTACT

function deleteAllContact(id)
{
  let text = "are you sure you want to delete all contact?";

  if (confirm(text) === true)
  {
    $.ajax({
      type: "GET",
      url: "script/delete.php?id=" + id + "&deleteAllContact=Y"
    }).done(function()
    {
        alert('Tout les contacts ont été supprimés.');
        window.location.reload();
    });
  }

}


// DELETE THIS IMAGE

function deleteThisImage(id)
{
  let text = "are you sure you want to delete this image?";

  if (confirm(text) === true)
  {
    $.ajax({
      type: "GET",
      url: "script/delete.php?id=" + id + "&deleteThisImage=Y"
    }).done(function()
    {
        alert('l\'image a été supprimé.');
        window.location.reload();
    });
  }
}


// DELETE ALL IMAGE

function deleteAllImage(id)
{
  let text = "are you sure you want to delete all images?";

  if (confirm(text) === true)
  {
    $.ajax({
      type: "GET",
      url: "script/delete.php?id=" + id + "&deleteAllImage=Y"
    }).done(function()
    {
        alert('les images ont été supprimés.');
        window.location.reload();
    });
  }
}


// DELETE THIS CHAT

function deleteThisChat(id)
{
  let text = "are you sure you want to delete this chat?";

  if (confirm(text) === true)
  {
    $.ajax({
      type: "GET",
      url: "script/delete.php?id=" + id + "&deleteThisChat=Y"
    }).done(function()
    {
      alert('le chat a été supprimé.');
    });
  }
}


// DELETE THIS MEMBER FROM CHAT

function deleteThisMemberFromChat(id)
{
  let text = "are you sure you want to delete this member from chat?";

  if (confirm(text) === true)
  {
    $.ajax({
      type: "GET",
      url: "script/delete.php?id=" + id + "&deleteThisMemberFromChat=Y"
    }).done(function()
    {
      alert('le membre a été supprimé.');
    });
  }
}


// DELETE MEMBER FROM ADMIN

function deleteMemberFromAdmin(id)
{
  let text = "are you sure you want to delete this member?";

  if (confirm(text) === true)
  {
    $.ajax({
      type: "GET",
      url: "script/delete.php?id=" + id + "&deleteThisMemberFromAdmin=Y"
    }).done(function()
    {
      alert('L\'utilisateur a été supprimé.');
      window.location.reload();
    });
  }
}


// BAN USER

function banUser(id)
{
  let text = "are you sure you want to ban this member?";

  if (confirm(text) === true)
  {
    $.ajax({
      type: "GET",
      url: "script/delete.php?id=" + id + "&banThisMember=Y"
    }).done(function()
    {
      alert('ban user.');
      window.location.reload();
    });
  }
}


// DELETE THIS MEMBER

function deleteThisMember(id)
{
  let text = "are you sure you want to delete your account?";

  if (confirm(text) === true)
  {
    $.ajax({
        type: "GET",
        url: "script/delete.php?id=" + id + "&deleteThisMember=Y"
    }).done(function()
    {
        alert('Your account have be deleted.');
        var newPatch = 'login';
        window.location.replace(newPatch);
    });
  }
}


// MENU ACCORDEON

const accordionItemHeaders = document.querySelectorAll(".accordion");

accordionItemHeaders.forEach(accordionItemHeader =>
{
  accordionItemHeader.addEventListener("click", event =>
  {
    accordionItemHeader.classList.toggle("active");
    const accordionItemBody = accordionItemHeader.nextElementSibling;

    if(accordionItemHeader.classList.contains("active"))
    {
      accordionItemBody.style.maxHeight = accordionItemBody.scrollHeight + "px";
    }

    else
    {
      accordionItemBody.style.maxHeight = 0;
    }
    
  });
});

// DEFAULT ON RESET LA HEIGHT A 0

var x = window.matchMedia("(min-width: 960px)")

function myFunction(x)
{
  if (x.matches)
  { // If media query matches
    $(".accordion-item").css("max-height", "");
  }

  else
  {
    $('.accordion-item').css("max-height", 0);
  }
}


myFunction(x) // Call listener function at run time
x.addListener(myFunction) // Attach listener function on state changes
