require('./bootstrap')
import { createApp } from 'vue'

import { configure, defineRule } from 'vee-validate'


configure({
    validateOnBlur: false,
    validateOnChange: false,
    validateOnInput: true,
    validateOnModelUpdate: false
})
const app = createApp({})
defineRule('password_rule', (value) => {
    return /^[A-Za-z0-9]*$/i.test(value)
})

defineRule('telephone', (value) => {
    return (
        /^0(\d-\d{4}-\d{4})+$/i.test(value.trim()) ||
        /^0(\d{3}-\d{2}-\d{4})+$/i.test(value.trim()) ||
        /^(070|080|090|050)(-\d{4}-\d{4})+$/i.test(value.trim()) ||
        /^0(\d{2}-\d{3}-\d{4})+$/i.test(value.trim()) ||
        /^0(\d{9,10})+$/i.test(value.trim())
    )
})

$(document).ready(function () {
    $(".select2").select2({
        multiple: true,
    });
})
$(document).ready(function () {

    const paginationNumbers = document.getElementById("pagination-numbers");
    if (paginationNumbers) {
        const paginatedList = document.getElementById("paginated-list");
        const listItems = paginatedList.querySelectorAll(".render-job-search");

        const paginationLimit = 4;
        const pageCount = Math.ceil(listItems.length / paginationLimit);
        let currentPage = 1;

        const appendPageNumber = (index) => {
            const pageNumber = document.createElement("button");
            pageNumber.className = "pagination-number";
            pageNumber.innerHTML = index;
            pageNumber.setAttribute("page-index", index);
            pageNumber.setAttribute("aria-label", "Page " + index);

            paginationNumbers.appendChild(pageNumber);
        };

        const getPaginationNumbers = () => {
            for (let i = 1; i <= pageCount; i++) {
                appendPageNumber(i);
            }
        };

        const handleActivePageNumber = () => {
            document.querySelectorAll(".pagination-number").forEach((button) => {
                button.classList.remove("active");
                const pageIndex = Number(button.getAttribute("page-index"));
                if (pageIndex == currentPage) {
                    button.classList.add("active");
                }
            });
        };

        const setCurrentPage = (pageNum) => {
            currentPage = pageNum;
            handleActivePageNumber();

            const prevRange = (pageNum - 1) * paginationLimit;
            const currRange = pageNum * paginationLimit;

            listItems.forEach((item, index) => {
                item.classList.add("hidden");
                if (index >= prevRange && index < currRange) {
                    item.classList.remove("hidden");
                }
            });
        };

        window.addEventListener("load", () => {
            getPaginationNumbers();
            setCurrentPage(1);

            document.querySelectorAll(".pagination-number").forEach((button) => {
                const pageIndex = Number(button.getAttribute("page-index"));

                if (pageIndex) {
                    button.addEventListener("click", () => {
                        setCurrentPage(pageIndex);
                    });
                }
            });
        });
    }
});


import VueSweetalert2 from 'vue-sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'
app.use(VueSweetalert2)

import userProfile from "./components/client/seeker/profile.vue";
app.component('user-profile', userProfile);
import userUploadCv from "./components/client/seeker/uploadcv.vue";
app.component('user-uploadcv', userUploadCv);
import showNew from "./components/client/home/show-new.vue";
app.component('show-new', showNew);
import ChangePassword from "./components/client/seeker/change-password.vue";
app.component('change-password', ChangePassword);
import popup from './components/common/popupAlert.vue'
app.component('popup-alert', popup)
import Upcv from './components/client/home/upcv.vue';
app.component('up-cv', Upcv)
import clientLogin from "./components/client/login/index.vue";
app.component('client-login', clientLogin);

import registerEmployer from "./components/employer/register.vue";
app.component('register-employer', registerEmployer);
import Notyf from "./components/common/notyf.vue";
app.component("notyf", Notyf);
import showCvClient from "./components/client/seeker/create-cv.vue";
app.component('show-cv-client', showCvClient);

app.mount('#app')
