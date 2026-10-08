var table;

function flattenObject(obj, prefix = "", result = {}) {
    for (const key of Object.keys(obj || {})) {
        const value = obj[key];
        const newKey = prefix ? prefix + "." + key : key;

        if (value && typeof value === "object" && !Array.isArray(value)) {
            flattenObject(value, newKey, result);
        } else {
            result[newKey] = Array.isArray(value)
                ? value.join(", ")
                : value == null ? "" : value;
        }
    }
    return result;
}

async function getGMBSPYSUser() {
    const session = await GMBSPYS_AUTH.getSession();

    if (!session?.access_token) {
        throw new Error("Please login to GMBSPYS first.");
    }

    let user = await GMBSPYS_AUTH.getUser();

    if (!user && session.refresh_token) {
        await GMBSPYS_AUTH.refreshSession();
        user = await GMBSPYS_AUTH.getUser();
    }

    if (!user) {
        throw new Error("Session expired. Please login again.");
    }

    return user;
}

function showAccount(user) {
    const el = document.getElementById("accountinfo");
    if (el) el.textContent = "Logged in as: " + (user.email || "GMBSPYS User");

    const upgrade = document.getElementById("upgradebtn");
    if (upgrade) upgrade.style.display = "none";
}

async function showData() {
    try {
        const stored = await chrome.storage.local.get([
            "gmb_scraper_leads",
            "leads"
        ]);

        let leads = [];

        const saved = stored.gmb_scraper_leads;

        if (Array.isArray(saved)) {
            leads = saved;
        } else if (saved && Array.isArray(saved.leads)) {
            leads = saved.leads;
        }

        if (!leads.length && Array.isArray(stored.leads)) {
            leads = stored.leads;
        }

        const rows = leads.map(item => flattenObject(item));
        const columnsSet = new Set();

        rows.forEach(row => Object.keys(row).forEach(key => columnsSet.add(key)));

        const columns = Array.from(columnsSet).map(key => ({
            title: key,
            field: key,
            headerSort: true,
            tooltip: true
        }));

        table.setColumns(columns);
        table.setData(rows);

        console.log("GMBSPYS: Loaded leads:", rows.length);

        if (!rows.length) {
            console.warn("No saved leads found in extension storage.");
        }
    } catch (error) {
        console.error("Could not load saved leads:", error);
        alert("Could not load leads. Check the extension Console.");
    }
}

async function downloadData(format) {
    try {
        await getGMBSPYSUser();

        const rows = table.getData();

        if (!rows.length) {
            alert("No leads loaded. Return to Google Maps and click Export Results again.");
            return;
        }

        if (format === "xlsx") {
            table.download("xlsx", "gmbspys-results.xlsx", {
                sheetName: "GMBSPYS Leads"
            });
        } else {
            table.download("csv", "gmbspys-results.csv");
        }
    } catch (error) {
        console.error("Export error:", error);
        alert(error.message || "Export failed.");
    }
}

document.addEventListener("DOMContentLoaded", async function () {
    table = new Tabulator("#example-table", {
        data: [],
        columns: [],
        layout: "fitDataStretch",
        placeholder: "No data available",
        downloadConfig: {
            columnHeaders: true
        }
    });

    table.on("tableBuilt", showData);

    document.getElementById("download-csv")?.addEventListener(
        "click", () => downloadData("csv")
    );

    document.getElementById("download-xlsx")?.addEventListener(
        "click", () => downloadData("xlsx")
    );

    try {
        const user = await getGMBSPYSUser();
        showAccount(user);
    } catch (error) {
        console.warn("GMBSPYS authentication:", error.message);
    }
});
