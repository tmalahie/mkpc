// Throttling logic for image loading
let iconLoadResolvers = [];
// Counter for active image loads
let activeIconLoadCount = 0;

// Loads all circuit images on the page
async function loadCircuitImages(delay=100) {

    const circuitElements = document.querySelectorAll("[data-cicon]");
    if (!circuitElements.length) return;

    for (const isMultiCup of [false, true]) { // Multicups first, then others
        for (const circuitElement of circuitElements) {
            const imageData = circuitElement.dataset.cicon;
            if (!imageData) continue;

            const isElementMultiCup = imageData.endsWith("&type=4");
            if (isElementMultiCup !== isMultiCup) continue;

            delete circuitElement.dataset.cicon;

            const imageUrls = imageData.split(",");
            const backgroundImages = imageUrls.map((url) => url);

            for (const [index, _] of backgroundImages.entries()) {
                await loadCircuitImage(circuitElement, backgroundImages, index, { delay, isMultiCup });
            }
        }
    }
}

// Loads a single circuit image and updates the background
async function loadCircuitImage(circuitElement, bgs, index, options) {
	const placeholders = Array(bgs.length).fill("url('images/uploads/overload.png')");
	circuitElement.style.backgroundImage = placeholders.join(",");
	
	await (new Promise((r) => setTimeout(r, options.delay)));
	
	// Throttle the number of concurrent image loads
	while (activeIconLoadCount > 25) {
		await waitForNextIconLoad();
	}
	
	for (let i = 0; i <= index; i++) {
		const imageUrl = bgs[i];
		const loadIncrement = options.isMultiCup ? 20 : 1;
		
		const imageElement = new Image();
		imageElement.src = imageUrl;
		
		activeIconLoadCount += loadIncrement;
		
		imageElement.onload = () => {
			activeIconLoadCount -= loadIncrement;
			placeholders[i] = `url('${imageUrl}')`;
			circuitElement.style.backgroundImage = placeholders.join(",");
			resolveNextIconLoad();
		};
		
		imageElement.onerror = () => {
			activeIconLoadCount -= loadIncrement;
			resolveNextIconLoad();
		};
	}
}

function waitForNextIconLoad() {
	return new Promise((resolve) => {
		iconLoadResolvers.push(resolve);
	});
}

function resolveNextIconLoad() {
	for (const resolve of iconLoadResolvers) {
		resolve();
	}
	iconLoadResolvers = [];
}

// Initialize image loading when the document is ready
if (document.readyState !== "loading") {
    loadCircuitImages();
} else {
    document.addEventListener("DOMContentLoaded", () => loadCircuitImages());
}