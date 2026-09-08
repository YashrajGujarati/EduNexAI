/* =========================================================
   EduNexAI - Admin Dashboard JavaScript
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    console.log("EduNexAI Dashboard Loaded Successfully");


    /* =====================================================
       ACTIVE SIDEBAR MENU
    ===================================================== */

    const currentPage =
        window.location.pathname.split("/").pop();

    const menuLinks =
        document.querySelectorAll(".sidebar ul li a");


    menuLinks.forEach(function (link) {

        const href =
            link.getAttribute("href");

        if (!href) {
            return;
        }

        const pageName =
            href.split("/").pop();

        if (pageName === currentPage) {

            link.classList.add("active");

        }

    });


    /* =====================================================
       DASHBOARD CARD ANIMATION
    ===================================================== */

    const cards =
        document.querySelectorAll(".dashboard-card");


    cards.forEach(function (card, index) {

        card.style.opacity = "0";

        card.style.transform =
            "translateY(15px)";


        setTimeout(function () {

            card.style.transition =
                "opacity 0.45s ease, transform 0.45s ease";

            card.style.opacity = "1";

            card.style.transform =
                "translateY(0)";

        }, 100 + (index * 100));

    });


    /* =====================================================
       CHART CARD ANIMATION
    ===================================================== */

    const chartCards =
        document.querySelectorAll(
            ".chart-card, .performance-card, .attendance-card, .analytics-card"
        );


    chartCards.forEach(function (card, index) {

        card.style.opacity = "0";

        card.style.transform =
            "translateY(15px)";


        setTimeout(function () {

            card.style.transition =
                "opacity 0.5s ease, transform 0.5s ease";

            card.style.opacity = "1";

            card.style.transform =
                "translateY(0)";

        }, 500 + (index * 150));

    });

});