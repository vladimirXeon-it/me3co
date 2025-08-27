// let resizer = document.querySelector('.frame_resizer');
// let frameWrapper = document.querySelector('#frame-wrapper')
// let prevY = 0;
// let active = false;
// resizer.addEventListener('mousedown', (e) => {
//     active = true
//     prevY = e.clientY
// })
// document.addEventListener('mousemove', (e) => {
//     if(!active) return;
//     let delY = e.clientY - prevY
//     let height = frameWrapper.clientHeight;
//     frameWrapper.style.height = `${height - delY}px`
//     prevY = e.clientY
// })
// document.addEventListener('mouseup', (e) => {
//     active = false
// })
let frameWrapper = document.querySelector('#frame-wrapper')
let frame = document.querySelector('.app_frame')
let buttons = document.querySelectorAll('.btn-open-project');
let closeButtons = document.querySelectorAll('.btn-close-project');
let projectToggle = document.querySelector('.project-toggle');
let { localStorage } = window

buttons.forEach(btn => {
    btn.addEventListener('click', (e) => {
        let url = e.target.dataset.href;
        localStorage.setItem('url', url)
        frame.src = url
        window.scrollTo(0, 0)
        frameWrapper.classList.add('maximized')
    })
})
closeButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
        localStorage.removeItem('url')
        frameWrapper.removeAttribute('class')
        frame.src = ''
        
    })
})

