document.addEventListener("DOMContentLoaded", () => {
    const fTopics = document.querySelectorAll(".fMessages");

    fTopics.forEach(fTopic => {
        const topicID = fTopic.dataset.topic;
        const fMessages = fTopic.querySelectorAll(".fMessage");

        fMessages.forEach(fMessage => {
            const msgID = fMessage.dataset.msg;

            fMessage.onclick = () => {
                open(`topic.php?topic=${topicID}&message=${msgID}`);
            };

            const fLinks = fMessage.querySelectorAll("a");
            fLinks.forEach(fLink => {
				fLink.addEventListener("click", (event) => event.stopPropagation());
            });
        });
    });
});