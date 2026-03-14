

    const toggle = document.getElementById("profileToggle");
    const menu = document.getElementById("dropdownMenu");

    toggle.addEventListener("click", function(){
    menu.classList.toggle("show");
});

    document.addEventListener("click", function(event){
    if(!toggle.contains(event.target) && !menu.contains(event.target)){
    menu.classList.remove("show");
}
});

