window.onload = function () {
    const useNodeJS = false;   // if you are not using a node server, set this value to false
    const defaultLiffId = "1653797762-GzLaLong";   // change the default LIFF value if you are not using a node server

    // DO NOT CHANGE THIS
    let myLiffId = "";

    // if node is used, fetch the environment variable and pass it to the LIFF method
    // otherwise, pass defaultLiffId
    if (useNodeJS) {
        fetch('/send-id')
            .then(function (reqResponse) {
                return reqResponse.json();
            })
            .then(function (jsonResponse) {
                myLiffId = jsonResponse.id;
                initializeLiffOrDie(myLiffId);
            })
            .catch(function (error) {
                document.getElementById("liffAppContent").classList.add('hidden');
                document.getElementById("nodeLiffIdErrorMessage").classList.remove('hidden');
            });
    } else {
        myLiffId = defaultLiffId;
        initializeLiffOrDie(myLiffId);
    }
};

/**
* Check if myLiffId is null. If null do not initiate liff.
* @param {string} myLiffId The LIFF ID of the selected element
*/
function initializeLiffOrDie(myLiffId) {
    if (!myLiffId) {
        console.log('liffId is not registered');
    } else {
        initializeLiff(myLiffId);
    }
}

/**
* Initialize LIFF
* @param {string} myLiffId The LIFF ID of the selected element
*/
function initializeLiff(myLiffId) {
    liff
        .init({
            liffId: myLiffId
        })
        .then(() => {
            // start to use LIFF's api
            console.log('liff initialized!');
        })
        .catch((err) => {
            console.log('failed to initialized LIFF app');
        });
}