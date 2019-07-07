'use strict';
const now = new Date();
const DELAY_CARS = 250, DELAY_IDS = 500, DELAY_TRY_AGAIN = 3000;

let pageIndex = 1, modelIndex = 1;


function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function rnd(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

async function request(payload) {
    return new Promise(function (resolve, reject) {
        if (payload.url !== undefined && payload.url !== null) {
            payload.url = encodeURI(payload.url);
        }
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'query.php', true);
        xhr.setRequestHeader('Content-Type', "application/json;charset=UTF-8");
        xhr.onload = function (e) {
            if (xhr.status === 200) {
                resolve(xhr.response)
            } else {
                resolve(xhr.status);
            }
        }
        xhr.send(JSON.stringify(payload));
    });
}

function tryParseJSON(json) {
    try {
        return JSON.parse(json);
    } catch (err) {
        console.log(err);
        console.log(json);
        return null;
    }
}


var Log = (function () {
    function i(msg) {
        let ta = document.getElementById('mainOutput');
        ta.innerText += msg;
    }

    function clear() {
        let ta = document.getElementById('mainOutput');
        ta.innerText = "";
    }

    var publicAPI = {
        clear: clear,
        i: i
    }

    return publicAPI;
})();



function updateUI(info) {
    let rexp;
    rexp = new RegExp('(?<=Make: ).*?(?=\/)');
    let makeFrom = (rexp.exec(info) !== null) ? (rexp.exec(info)[0]).trim() : null;
    rexp = new RegExp('(?<= Model: ).*?(?=\/)');
    let model = (rexp.exec(info) !== null) ? (rexp.exec(info)[0]).trim() : null;
    rexp = new RegExp('(?<= Page: ).*');
    let page = (rexp.exec(info) !== null) ? (rexp.exec(info)[0]).trim() : null;

    localStorage.setItem('sc_autotrader:date', now);
    if (makeFrom !== null) txtMakeIndexFrom.value = makeFrom; localStorage.setItem('sc_autotrader:makeFrom', makeFrom);

    if (model !== null) { txtModelIndex.value = model; localStorage.setItem('sc_autotrader:model', model) } else { localStorage.setItem('sc_autotrader:model', '1') };
    if (page !== null) txtPageIndex.value = page; localStorage.setItem('sc_autotrader:page', page);
}

// 177 proxy
let myProxies = ["217.113.122.142:3128", "35.189.90.214:3128", "159.8.114.37:80", "89.218.177.179:3128", "54.37.22.173:3128", "159.8.114.37:25", "157.230.44.168:1111", "91.221.109.138:3128", "91.221.109.136:3128", "84.201.254.47:3128", "51.38.11.229:3128", "221.126.249.101:8080", "173.192.21.89:80", "161.202.226.194:8123", "51.15.210.208:3128", "173.249.30.197:8118", "188.138.235.56:3128", "144.76.82.111:3128", "104.248.168.59:3128", "51.77.213.22:3128", "35.193.0.225:80", "119.81.189.194:8123", "104.248.89.208:8080", "104.248.168.59:8080", "178.128.63.155:31330", "185.80.130.17:80", "173.192.21.89:25", "23.237.22.87:3128", "40.118.127.197:8080", "192.166.219.46:3128", "104.248.84.77:3128", "157.230.33.37:1111", "91.235.42.20:3130", "104.248.154.97:1111", "23.237.22.82:3128", "221.126.249.102:8080", "54.37.6.196:3128", "109.123.4.12:3128", "157.230.34.190:1111", "134.19.218.94:3129", "193.92.85.51:8080", "176.106.229.51:8080", "95.88.192.108:3128", "119.81.71.27:80", "5.79.113.168:3128", "51.68.177.232:1080", "54.37.148.84:3128", "104.248.89.208:3128", "118.174.211.222:81", "50.235.28.146:3128", "178.128.222.29:3128", "18.221.92.110:3128", "221.126.249.100:8080", "210.0.128.58:8080", "118.174.211.222:8080", "47.89.253.35:3128", "178.128.222.29:8080", "68.183.183.44:1111", "221.126.249.99:8080", "163.28.10.120:80", "173.192.21.89:8123", "74.208.83.188:80", "157.230.137.96:3128", "79.123.136.96:3128", "157.230.44.168:1111", "173.192.21.89:25", "23.237.22.82:3128", "51.68.177.232:1080", "119.81.189.194:8123", "159.8.114.37:80", "173.192.21.89:80", "35.212.155.228:3128", "192.166.219.46:3128", "178.128.80.246:3128", "158.69.59.171:3128", "149.56.102.220:3128", "142.4.196.107:3128", "54.39.97.250:3128", "157.230.8.128:8080", "138.197.128.33:8080", "167.99.7.198:8080", "159.65.226.80:3128", "68.183.121.154:8080", "157.230.90.113:8080", "142.93.195.94:8080", "159.203.65.214:8080", "104.248.7.88:80", "204.48.18.225:8080", "45.79.159.195:3128", "178.128.151.123:8080", "167.114.79.139:35869", "159.89.119.45:8080", "68.183.103.88:8080", "159.65.186.239:3128", "144.217.86.131:3128", "54.39.148.240:80", "159.89.236.26:8080", "162.243.102.207:3128", "54.84.154.208:3128", "138.197.108.5:3128", "165.227.42.21:8080", "40.114.109.214:3128", "165.225.3.33:8800", "35.174.95.137:80", "165.225.3.35:8800", "134.209.119.225:8080", "159.203.119.119:3128", "142.93.63.115:8080", "165.225.3.39:8800", "165.225.3.34:8800", "68.183.143.161:8080", "54.208.148.7:3128", "72.38.127.210:35121", "165.227.35.172:8080", "35.229.113.175:443", "54.83.61.248:3128", "198.27.67.35:3128", "159.203.19.216:8080", "104.236.17.72:8118", "159.65.229.150:8080", "45.77.149.240:80", "52.22.122.114:3128", "3.87.85.38:3128", "198.27.69.221:81", "104.248.236.12:8080", "47.90.208.75:808", "18.223.164.142:3128", "34.228.180.50:3128", "23.253.207.55:3128", "52.91.127.80:3128", "18.223.143.239:8080", "162.245.81.238:3128", "3.18.221.63:8118", "68.142.183.89:80", "104.222.110.66:23500", "3.17.175.232:3128", "138.197.208.221:8080", "47.48.238.149:8080", "23.237.22.87:3128", "70.169.150.122:48678", "52.144.96.93:8080", "165.225.3.41:8800", "35.184.159.21:8080", "34.66.7.159:80", "35.225.204.126:80", "157.230.208.237:808", "165.225.3.10:8800", "209.97.191.169:3128", "66.113.180.122:3128", "134.209.13.153:8080", "157.230.137.96:3128", "35.222.211.82:3128", "172.82.152.229:3128", "95.85.25.124:4444", "198.11.178.14:8080", "162.220.108.59:80", "134.119.188.148:1080", "165.225.3.42:8800", "97.92.111.244:443", "206.125.41.130:80", "206.189.117.237:8080", "190.121.227.174:3128", "206.189.114.168:8080", "104.248.168.59:3128", "46.101.246.130:3128", "167.99.197.24:8080", "187.188.211.73:9991", "104.248.168.64:3128", "165.225.3.37:8800", "151.106.8.234:8080", "178.128.174.206:3128", "141.193.189.1:44464", "149.28.72.247:443", "206.189.112.106:3128", "209.97.177.138:8080", "159.65.89.244:8080", "159.65.92.98:8080", "51.75.109.86:3128", "64.235.204.107:8080"];

function removeProxy(proxy) {
    // in case there are more than one copies of the same bad proxy server, remove them all!!!
    for (var i = myProxies.length - 1; i--;) {
        if (myProxies[i] == proxy) myProxies.splice(i, 1);
    }
    Log.i(` Proxy: ${proxy} has been removed\r\n`);
}

async function scrapeCarInfo(payload) {
    await sleep(DELAY_CARS);

    var t0 = performance.now();
    const res = JSON.parse(await request(payload));
    if (res.ERR_CODE == "NO_RESPONSE") {
        if (res.PROXY != null) { removeProxy(res.PROXY); }
        payload.proxy = myProxies[rnd(0, myProxies.length - 1)];
        console.log('setting up new proxy');
        console.log(payload.proxy);
        Log.i('>');
        await scrapeCarInfo(payload);
    } else {
        // Success
        var t1 = performance.now();
        Log.i(`${res.index} ${(res.notes != null) ? res.notes : ''} in ${(t1 - t0).toFixed(0)} ms\r\n`);
    }
}



async function scrapePages(payload, lastPage, info) {
    const url = payload.url;
    payload.action = 'GET_IDS';

    // Get some random proxy to work with
    payload.proxy = myProxies[rnd(0, myProxies.length - 1)];

    for (let i =  pageIndex; i <= lastPage; i++) {
        Log.i(`${info}, Page: ${i} / ${lastPage} ...\r\n`);
        payload.url = (url + "&page=" + i);
        let res = JSON.parse(await request(payload));
        payload.url = (url);

        if (res.ERR_CODE == "EMPTY_IDS" || res.ERR_CODE == "NO_RESPONSE") {
            // Change proxy
            Log.clear();
            removeProxy(payload.proxy);
            payload.proxy = myProxies[rnd(0, myProxies.length - 1)];
            Log.i(`New proxy: ${payload.proxy}\r\n`);

            // Recursion!
            //await sleep(DELAY_TRY_AGAIN);
            pageIndex = i;
            await scrapePages(payload, lastPage, info);
        } else {
            Log.i(`Processing cars\r\n`);

            for (let j = 0; j < res.length; j++) {
                Log.i(`Scraping ${res[j]}: `);
                await scrapeCarInfo({ 'action': 'GET_CAR_INFO', 'id': res[j] });
            }

            updateUI(`${info}, Page: ${i}`);
            Log.clear();
            pageIndex = 1;
        }
    }
}

function getNumber(input, def) {
    let res = (def != undefined) ? def : 0;
    if (input != null && input != "" && input != undefined) {
        if (Number.isNaN(Number(input))) {
            if (input.substr(-1, 1) == "к" || input.substr(-1, 1) == "k") {
                let numPart = input.substring(0, input.length - 1);
                res = Number(numPart) * 1000;
            }
        } else {
            res = Number(input);
        }
    }
    return res;
}


async function btnStart() {
    Log.clear();
    Log.i("Starting... \r\n");
    let data = JSON.parse(await request({ 'action': 'JSON_FROM_FILE', 'fileName': 'cars.json' }));

    let ageRange = [
        { from: 1980, to: 2004 },
        { from: 2005, to: 2007 },
        { from: 2008, to: 2009 },
        { from: 2010, to: 2011 },
        { from: 2012, to: 2013 },
        { from: 2014, to: 2015 },
        { from: 2016, to: 2017 },
        { from: 2018, to: 2019 }];

    //117

    let makeIndexFrom = getNumber(txtMakeIndexFrom.value) - 1;
    let makeIndexTo = getNumber(txtMakeIndexTo.value) - 1;
    pageIndex = getNumber(txtPageIndex.value, 1);
    modelIndex = getNumber(txtModelIndex.value, 1) - 1;

    localStorage.setItem('sc_autotrader:makeTo', makeIndexTo);

    txtMakeIndexFrom.disabled = "disabled";
    txtMakeIndexTo.disabled = "disabled";
    txtModelIndex.disabled = "disabled";
    txtPageIndex.disabled = "disabled";

    for (let i = makeIndexFrom; i <= makeIndexTo; i++) {
        const manufacturer = data[i];

        let reqStr = `https://www.autotrader.co.uk/results-car-search?sort=recommended&radius=1500&postcode=e161xl&onesearchad=Used&onesearchad=Nearly+New&onesearchad=New&make=${manufacturer.title}&writeoff-categories=on`;
        /*  
        IF make.count <1000 THEN GO TO PAGES
        ELSE IF model.count <1200 THEN GO TO PAGES 
        ELSE ADDRANGE, LOOP THROUGH MODELS
        */
        let lastPage;
        if (manufacturer.total > 1100) {
            for (let j = modelIndex; j < manufacturer.models.length; j++) {
                let model = manufacturer.models[j];
                let m_appendix = `&model=${model.title}`;

                if (model.count > 1100) {
                    for (let k = 0; k < ageRange.length; k++) {
                        lastPage = Math.floor((Math.floor(model.count / ageRange.length) / 10)+1);
                        let y_appendix = `&year-from=${ageRange[k].from}&year-to=${ageRange[k].to}`;
                        await scrapePages({ 'url': reqStr + m_appendix + y_appendix }, lastPage, `Make: ${i + 1} / ${data.length} (${manufacturer.title}), Model: ${j + 1} / ${manufacturer.models.length} (${model.title}), Range: ${k + 1} / ${ageRange.length}`);
                    }
                } else {
                    lastPage = Math.floor(model.count / 10)+1;
                    await scrapePages({ 'url': reqStr + m_appendix }, lastPage, `Make: ${i + 1} / ${data.length} (${manufacturer.title}), Model: ${j + 1} / ${manufacturer.models.length} (${model.title})`);
                }
            }
        } else {
            lastPage = Math.floor(manufacturer.total / 10)+1;
            await scrapePages({ 'url': reqStr }, lastPage, `Make: ${i + 1} / ${data.length} (${manufacturer.title})`);
        }
    }

    Log.i('Done');
}
var txtMakeIndexFrom, txtMakeIndexTo, txtModelIndex, txtPageIndex;

function init() {
    txtMakeIndexFrom = document.getElementById('makeIndexFrom');
    txtMakeIndexTo = document.getElementById('makeIndexTo');
    txtModelIndex = document.getElementById('modelIndex');
    txtPageIndex = document.getElementById('pageIndex');

    const makeFrom = localStorage.getItem('sc_autotrader:makeFrom');
    const makeTo = localStorage.getItem('sc_autotrader:makeTo');
    const model = localStorage.getItem('sc_autotrader:model');
    const page = localStorage.getItem('sc_autotrader:page');

    if (makeFrom !== null) txtMakeIndexFrom.value = makeFrom;
    if (makeTo !== null) txtMakeIndexTo.value = makeTo;
    if (model !== null) txtModelIndex.value = model;
    if (page !== null) txtPageIndex.value = page;
};