function apercu(imageSources) {
    let imageList;
    if (typeof imageSources === "object") {
        imageList = imageSources;
    } else {
        imageList = [imageSources];
    }

    const items = imageList.map((src) => {
        return {
            src: src,
            w: 0, // Placeholder width
            h: 0  // Placeholder height
        };
    });

    const pswpElement = document.querySelectorAll('.pswp')[0];

    // Dynamically load the first image dimensions
    const firstImage = new Image();
    firstImage.onload = function () {
        items[0].w = this.naturalWidth;
        items[0].h = this.naturalHeight;

        // Open PhotoSwipe after the first image loads
        const options = {
            index: 0, // Start at the first image
            bgOpacity: 0.85,
            showHideOpacity: true,
        };

        const gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
        gallery.init();

        // Handle focus management when closing PhotoSwipe
        gallery.listen('close', () => {
            // Attempt of avoiding console error with aria-hidden
            document.body.focus();
            pswpElement.setAttribute('aria-hidden', 'true');
        });

        // Load the rest of the images and update PhotoSwipe dynamically
        items.forEach((item, index) => {
            if (index === 0) return; // Skip the first image (already loaded)
            const img = new Image();
            img.onload = function () {
                item.w = this.naturalWidth;
                item.h = this.naturalHeight;

                // Update the PhotoSwipe item dimensions dynamically
                if (gallery.currItem === item) {
                    gallery.invalidateCurrItems(); // Invalidate current items
                    gallery.updateSize(true); // Update the gallery size
                }
            };
            img.src = item.src;
        });
    };
    firstImage.src = items[0].src;
}