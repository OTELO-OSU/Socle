import { symfony } from "../utils/axios";

function getFilenameFromContentDisposition(contentDisposition?: string): string | null {
    if (!contentDisposition) {
        return null;
    }

    const utf8Match = contentDisposition.match(/filename\*=UTF-8''([^;]+)/i);
    if (utf8Match?.[1]) {
        return decodeURIComponent(utf8Match[1]);
    }

    const asciiMatch = contentDisposition.match(/filename="?([^";]+)"?/i);
    if (asciiMatch?.[1]) {
        return asciiMatch[1];
    }

    return null;
}

function requestSinglePlateformeExport(plateformeId: number) {
    return symfony.get("/export/plateforme", {
        params: {
            id: plateformeId
        },
        responseType: "blob"
    });
}

function requestSingleHarvesterPlateformeExport(plateformeId: number, host: string) {
    return symfony.get("/harvester/export/plateforme", {
        params: {
            id: plateformeId,
            host: host
        },
        responseType: "blob"
    });
}

export async function downloadSinglePlateforme({ plateformeId, harvester, host }: { plateformeId: number, harvester: boolean, host: string }): Promise<void> {
    let response;
    if (harvester) {
        if (host) {
            response = await requestSingleHarvesterPlateformeExport(plateformeId, host);
        }
    } else {
        response = await requestSinglePlateformeExport(plateformeId);
    }

    if (!response) {
        return;
    }

    const blob = response.data as Blob;
    const filename =
        getFilenameFromContentDisposition(response.headers["content-disposition"]) ||
        `plateforme-${plateformeId}.xlsx`;

    const url = window.URL.createObjectURL(blob);
    const anchor = document.createElement("a");
    anchor.href = url;
    anchor.download = filename;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
    window.URL.revokeObjectURL(url);
}