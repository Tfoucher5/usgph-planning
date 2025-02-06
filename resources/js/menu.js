document.addEventListener("DOMContentLoaded", function (event) {

  const showNavbar = (toggleId, navId, bodyId, headerId) => {
    const toggle = document.getElementById(toggleId),
      nav = document.getElementById(navId),
      body = document.getElementById(bodyId),
      header = document.getElementById(headerId)

    // Validate that all variables exist
    if (toggle && nav && body && header) {
      toggle.addEventListener('click', () => {
        // show navbar
        nav.classList.toggle('show')
        // change icon
        toggle.classList.toggle('bx-x')
        // add padding to body
        body.classList.toggle('body')
        // add padding to header
        header.classList.toggle('body')
      })
    }
  }

  showNavbar('header-toggle', 'nav-bar', 'body', 'header')

  /*===== LINK ACTIVE =====*/
  const linkColor = document.querySelectorAll('.nav_link')

  function colorLink() {
    if (linkColor) {
      linkColor.forEach(l => l.classList.remove('menu-active'))
      this.classList.add('menu-active')
    }
  }
  linkColor.forEach(l => l.addEventListener('click', colorLink))

  // Your code to run since DOM is loaded and ready
});
