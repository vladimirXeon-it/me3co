let datalists = document.querySelectorAll('data-list');
let css = `input[list]:focus + data-list{
    display: block;
}
data-list{
    width: 100%;
    z-index: 9;
    position: absolute;
    display: none;
    height: fit-content;
    background-color: white;
    border: 1px solid rgba(0,0,0,0.1);
    transform: translateY(8px);
    padding: 2px;
    box-shadow: 0 0 12px rgba(0,0,0,0.07);
}
data-list option{
    padding: 12px 8px;
    font-size: 13px;
}
data-list option:hover{
    background-color: var(--data-list-hover);
    color: white;
}`;
let blob = new Blob([css], { type: "text/css;charset=utf-8" });
let src = URL.createObjectURL(blob)
document.querySelector('head').innerHTML += `<link rel="stylesheet" href="${src}">`
datalists.forEach((datalist) => {
    let options = datalist.querySelectorAll('option');
    let targets = document.querySelectorAll(`[list="${datalist.id}"]`) || document.querySelectorAll(`[list='${datalist.id}']`);
    targets.forEach(target => {
        target.addEventListener('input', (e) => {
            let value = e.target.value.toLowerCase();
            options.forEach(option => {
                if (option.textContent.toLowerCase().includes(value)) {
                    option.style.removeProperty('display')
                }
                else {
                    option.style.display = 'none'
                }
            })
        })
    })
    options.forEach(option => {
        option.addEventListener('mouseover', () => {
            let value = option.textContent;
            targets.forEach(target => { target.value = value })
        })
    })
})