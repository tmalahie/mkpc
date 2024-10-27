let iconDelayDt = 100;
let iconsLoadingCount = 0;
async function setCircuitImgs(icCircuit,opts) {
	let imgsData = icCircuit.dataset.cicon;
	if (imgsData) {
		let bgs = [];
		let isMCup = imgsData.endsWith("&type=4");
		if (isMCup !== opts.mcup)
			return;
		delete icCircuit.dataset.cicon;
		let imgs = imgsData.split(",");
		for (let j=0;j<imgs.length;j++)
			bgs[j] = imgs[j];
		for (let j=0;j<bgs.length;j++)
			await setCircuitImg(icCircuit,bgs,j,isMCup);
	}
}
async function setCircuitImg(icCircuit,bgs,j,isMCup) {
	let bgsIncomplete = [];
	for (let i=0;i<bgs.length;i++)
		bgsIncomplete[i] = "url('images/uploads/overload.png')";
	icCircuit.style.backgroundImage = bgsIncomplete.join(",");
	await waitIconDelay();
	while (iconsLoadingCount > 25)
		await waitNextIconLoad();
	for (let i=0;i<=j;i++) {
		let bgSrc = bgs[i];
		let loadingCountInc = isMCup ? 20 : 1;
		let $bg = new Image();
		$bg.src = bgSrc;
		iconsLoadingCount += loadingCountInc;
		$bg.onload = () => {
			iconsLoadingCount -= loadingCountInc;
			bgsIncomplete[i] = "url('"+bgSrc+"')";
			icCircuit.style.backgroundImage = bgsIncomplete.join(",");
			resolveNextIconLoad();
		};
		$bg.onerror = () => {
			iconsLoadingCount -= loadingCountInc;
			resolveNextIconLoad();
		};
	}
}
async function loadCircuitImgs() {
	let icCircuits = document.querySelectorAll("[data-cicon]");
	for (let i=0;i<icCircuits.length;i++)
		await setCircuitImgs(icCircuits[i],{mcup:false});
	for (let i=0;i<icCircuits.length;i++)
		await setCircuitImgs(icCircuits[i],{mcup:true});
}
let iconThrottledPromises = [];
function waitNextIconLoad() {
	let res = new Promise((resolve) => {
		iconThrottledPromises.push(resolve);
	});
	return res;
}
function resolveNextIconLoad() {
	for (let i=0;i<iconThrottledPromises.length;i++)
		iconThrottledPromises[i]();
	iconThrottledPromises = [];
}
function waitIconDelay() {
	return new Promise(resolve => setTimeout(resolve, iconDelayDt));
}
if ("loading" !== document.readyState)
	loadCircuitImgs();
else
	document.addEventListener("DOMContentLoaded", loadCircuitImgs);