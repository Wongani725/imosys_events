/**
 * Author: Jones Blackwell
 * License: MIT
 * @param options
 * @returns {string}
 * @constructor
 */

// actionTemplate = ActionPostLink({
//     csrf:"{{csrf_token()}}",
//     action:"{{route('faults_assign_technician')}}",
//     title: "submit",
//     classes:"",
//     unique_name: "fault_reference",
//     unique_value: `${row.reference}`,
//     style: "",
//     content: ""
// });

function DecodeEntities(s){
    var str, temp= document.createElement('p');
    temp.innerHTML= s;
    str= temp.textContent || temp.innerText;
    temp=null;
    return str;
}

function Span(options={}) {
    const attr = Object.assign({ classes: "", id: "", style: "", content: "", onclick: ""}, options);
    const onclick = attr.onclick !== "" ? `onclick="${attr.onclick}"` : "";
    return `<span class="${attr.classes}" id="${attr.id}" style="${attr.style}" ${onclick}>${attr.content}</span>`;
}


function Link(options={}) {
    const attr = Object.assign({url:"", classes: "", id: "", style: "", content: "", onclick: ""}, options);
    const onclick = attr.onclick !== "" ? `onclick="${attr.onclick}"` : "";
    return `<a href="${attr.url}" class="${attr.classes}" id="${attr.id}" style="${attr.style}" ${onclick}>${attr.content}</a>`;
}

function FormInput(options={}) {
    const attr = Object.assign({name:"", classes: "", id: "", style: "", value: "", placeholder: "", required: false, type: "text"}, options);
    const id = attr.id !== "" ? `id="${attr.id}"` : "";
    const required = attr.required ? `required` : "";

    return `<input type="${attr.type}" name="${attr.name}" class="${attr.classes}" ${id} ${required} value="${attr.value}" style="${attr.style}" placeholder="${attr.placeholder}">`;
}

function DataForm(options={}) {
    const attr = Object.assign({action:"", method: "POST", classes: "", id: "", style: "", content: "", onsubmit: ""}, options);
    const onsubmit = attr.onclick !== "" ? `onsubmit="${attr.onsubmit}"` : "";
    return `<form action="${attr.action}" method="${attr.method}" class="${attr.classes}" id="${attr.id}" style="${attr.style}" ${onsubmit}>${attr.content}</form>`;
}

function ActionPostLink(options={}){
    const attr = Object.assign({csrf:"", action:"", title: "submit", classes:"", unique_name: "unique_ref", unique_value:"", style: "", content: ""}, options);
    const identityInput = (attr.unique_name !== "" && attr.unique_value !== "") ? FormInput({name:attr.unique_name, value: attr.unique_value, type: "hidden"}) : "";
    const  formContent = `<input type="hidden" name="_token" value="${attr.csrf}"> ${identityInput} ${attr.content}`;

    return FormLink({
        link: {
            classes: attr.classes,
            id: attr.id,
            style: attr.id,
            content: attr.title,
            onclick: "event.preventDefault(); this.closest('form').submit();",
        },
        form: {
            action: attr.action,
            method: "POST",
            content: formContent,
        },
    });
}

function FormLink(options={}) {
    const attr = Object.assign({
        link: {
            url:"",
            classes: "",
            id: "",
            style: "",
            content: "",
            onclick: "",
        },
        form: {
            action: "",
            method: "POST",
            classes: "",
            id: "",
            content: ""
        }
    }, options);

    const link = Link(attr.link);
    const formAttr = attr.form;
    const formContent = `${formAttr.content} ${link}`;
    return DataForm({action: formAttr.action, method: formAttr.method, classes: formAttr.classes, id: formAttr.id, style: formAttr.style, content: formContent,})
}

function DataRowStatus(status, is_numeric=false) {

    var a = a.status, n = {
        1: {title: "Current", class: "bg-label-primary"},
        2: {title: "Professional", class: " bg-label-success"},
        3: {title: "Rejected", class: " bg-label-danger"},
        4: {title: "Resigned", class: " bg-label-warning"},
        5: {title: "Applied", class: " bg-label-info"}
    };
    return void 0 === n[a] ? e : '<span class="badge ' + n[a].class + '">' + n[a].title + "</span>"
}

function DataRowTextStatus(status) {
    switch (status.toLowerCase()) {
        case "active": case "professional": case "enabled": case "online":
            return `<span class="badge bg-label-success">${status}</span>`;
        case "disabled": case "suspended": case "resigned": case "inactive":
            return `<span class="badge bg-label-warning">${status}</span>`;
        case "deleted": case "removed": case "rejected": case "offline": case "invalid":
            return `<span class="badge bg-label-danger">${status}</span>`;
        case "applied": case "pending": case "waiting": case "initialised":
            return `<span class="badge bg-label-info">${status}</span>`;
        case "current": case "working": case "valid": default:
            return `<span class="badge bg-label-primary">${status}</span>`;
    }


    // {
    //     targets: -2, render: function (data, type, row, meta) {
    //     return (t === "integer") ? DataRowStatus(e,t,a,s) : DataRowTextStatus(e);
    //     }
    // },
}


function DataRowAvator() {
    return {
        targets: 3,
        responsivePriority: 4,
        render: function (e, t, a, s) {
            var n = a.avatar, l = a.faults_date, r = a.post;

            return '<div class="d-flex justify-content-start align-items-center user-name">' +
                '<div class="avatar-wrapper"><div class="avatar me-2">'
                + (n ? '<img src="' + assetsPath + "/img/avatars/" + n +
                    '" alt="Avatar" class="rounded-circle">' : '<span class="avatar-initial rounded-circle bg-label-'
                    + ["success", "danger", "warning", "info", "dark", "primary", "secondary"][Math.floor(6 * Math.random())]
                    + '">' + (n = (((n = (l = a.full_name).match(/\b\w/g) || []).shift() || "") + (n.pop() || "")).toUpperCase())
                    + "</span>") + '</div></div><div class="d-flex flex-column"><span class="emp_name text-truncate">'
                + l + '</span><small class="emp_post text-truncate text-muted">' + r + "</small></div></div>"
        }
    };
}

function DataRowActions() {
    return {
        targets: -1, title: "Actions", orderable: !1, searchable: !1, render: function (e, t, a, s) {
            return '<div class="d-inline-block">' +
                '<a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">' +
                '<i class="bx bx-dots-vertical-rounded"></i>' +
                '</a>' +
                '<ul class="dropdown-menu dropdown-menu-end m-0">' +
                '<li><a href="javascript:;" class="dropdown-item">Details</a></li>' +
                '<li><a href="javascript:;" class="dropdown-item">Archive</a></li>' +
                '<div class="dropdown-divider"></div>' +
                '<li><a href="javascript:;" class="dropdown-item text-danger delete-record">Delete</a></li>' +
                '</ul>' +
                '</div>' +
                '<a href="javascript:;" class="btn btn-sm btn-icon item-edit"><i class="bx bxs-edit"></i></a>'
        }
    };
}

function DataRowCheckbox() {
    return {
        targets: 1,
        orderable: !1,
        searchable: !1,
        responsivePriority: 3,
        checkboxes: !0,
        checkboxes: {selectAllRender: '<input type="checkbox" class="form-check-input">'},
        render: function () {
            return '<input type="checkbox" class="dt-checkboxes form-check-input">'
        }
    };
}


function DataRowCustomFilter() {
    // remove the outer curly brackets
    return {initComplete: function () {
        this.api().columns(2).every(function () {
            var t = this,
                a = $('<select id="UserRole" class="form-select text-capitalize"><option value=""> Select Role </option></select>').appendTo(".user_role").on("change", function () {
                    var e = $.fn.dataTable.util.escapeRegex($(this).val());
                    t.search(e ? "^" + e + "$" : "", !0, !1).draw()
                });
            t.data().unique().sort().each(function (e, t) {
                a.append('<option value="' + e + '">' + e + "</option>")
            })
        }), this.api().columns(3).every(function () {
            var t = this,
                a = $('<select id="UserPlan" class="form-select text-capitalize"><option value=""> Select Plan </option></select>').appendTo(".user_plan").on("change", function () {
                    var e = $.fn.dataTable.util.escapeRegex($(this).val());
                    t.search(e ? "^" + e + "$" : "", !0, !1).draw()
                });
            t.data().unique().sort().each(function (e, t) {
                a.append('<option value="' + e + '">' + e + "</option>")
            })
        }), this.api().columns(5).every(function () {
            var t = this,
                a = $('<select id="FilterTransaction" class="form-select text-capitalize"><option value=""> Select Status </option></select>').appendTo(".user_status").on("change", function () {
                    var e = $.fn.dataTable.util.escapeRegex($(this).val());
                    t.search(e ? "^" + e + "$" : "", !0, !1).draw()
                });
            t.data().unique().sort().each(function (e, t) {
                a.append('<option value="' + l[e].title + '" class="text-capitalize">' + l[e].title + "</option>")
            })
        })
    },};
}

function DataTableBasicButton() {
    return {
        text: '<i class="bx bx-plus me-sm-2"></i> <span class="d-none d-sm-inline-block">Add New Record</span>',
        className: "create-new btn btn-primary"
    };
}


function DataRowControl() {
    return {
        className: "control",
        orderable: !1,
        searchable: !1,
        responsivePriority: 2,
        targets: 0,
        render: function (e, t, a, s) {
            return ""
        }
    };
}

function DataRowHidden() {
    return {targets: 2, searchable: !1, visible: !1};
}
