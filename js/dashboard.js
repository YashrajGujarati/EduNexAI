window.addEventListener("load", function () {
    console.log("EduNexAI Dashboard Loaded Successfully");
});

const currentPage = window.location.pathname.split("/").pop();
const menuLinks = document.querySelectorAll(".sidebar ul li a");

menuLinks.forEach(function(link){
    const pageName = link.getAttribute("href");
    if(pageName === currentPage){
        link.classList.add("active");
    }
});

const cards = document.querySelectorAll(".dashboard-card");

cards.forEach(function(card,index){
    card.style.opacity="0";
    card.style.transform="translateY(20px)";
    setTimeout(function(){
        card.style.transition="0.5s";
        card.style.opacity="1";
        card.style.transform="translateY(0px)";
    },200*index);
});