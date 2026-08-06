window.addEventListener("load",function(){
    console.log("EduNexAI Loaded Successfully");
});
const nav=document.querySelector(".navbar");
window.addEventListener("scroll",function(){
    if(window.scrollY>50){
        nav.style.boxShadow="0 4px 15px rgba(0,0,0,0.2)";
    }
    else{
        nav.style.boxShadow="none";
    }
});