var xhr = new XMLHttpRequest();
xhr.open('GET', 'includes/navbar.inc.html', true);
xhr.onreadystatechange = function () {
  if (xhr.readyState === 4 && xhr.status === 200) {
    document.getElementById('navbarContainer').innerHTML = xhr.responseText;
  }
};
xhr.send();
