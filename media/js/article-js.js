window.addEventListener('DOMContentLoaded', function() {
  let sourceToggle = document.getElementById('sourceToggle')

  // Source block toggle
  if(sourceToggle !== null) {
    let sourceText = document.getElementById('articleSources'),
        stArrow    = sourceToggle.querySelector('span')

    sourceToggle.addEventListener('click', function(e) {
      // console.info(sourceText.style.display)

      if(sourceText.classList.contains('visible')) {
        sourceText.classList.remove('visible')

        stArrow.classList.remove('fa-angle-down')
        stArrow.classList.add('fa-angle-right')

      } else {
        sourceText.classList.add('visible')

        stArrow.classList.remove('fa-angle-right')

        stArrow.classList.add('fa-angle-down')
      }
    })
  }
})