const leadListObserver = () => {
    [...document.querySelectorAll('.crm-info-title-wrapper>a')].forEach(node => {
        if(!node.querySelector('mark') && node.textContent.match(/\[duplicate\]/gi)){
            node.innerHTML = node.textContent.replace('[duplicate]', '<mark style="background-color: #ffb4a9;">[duplicate]</mark>');
        }
    })
};

BX(() => {
    // Observe changes
    const observer = new MutationObserver(leadListObserver);
    observer.observe(document.querySelector('#CRM_LEAD_LIST_V12'), {
        attributes: false,
        childList: true,
        subtree: true
    });
});