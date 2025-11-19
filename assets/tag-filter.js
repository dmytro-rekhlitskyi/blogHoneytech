import Choices from 'choices.js';

document.addEventListener('DOMContentLoaded', function() {
    const tagFilterElement = document.getElementById('tag-filter');

    if (!tagFilterElement) {
        return;
    }

    let isProgrammaticChange = false;
    let filterTimeout = null;

    const choices = new Choices(tagFilterElement, {
        removeItemButton: true,
        placeholder: true,
        placeholderValue: 'Select a tag to filter',
        searchPlaceholderValue: 'Search tags...',
        shouldSort: false,
        searchChoices: false,
        searchFloor: 0,
    });

    async function loadTags(searchQuery = '') {
        try {
            const params = new URLSearchParams({
                limit: '100',
            });

            if (searchQuery && searchQuery.trim()) {
                params.append('query', searchQuery.trim());
            }

            const response = await fetch(`/api/tags?${params.toString()}`);
            const data = await response.json();

            return data.data.map(tag => ({
                value: tag.slug,
                label: tag.name,
            }));
        } catch (error) {
            console.error('Error loading tags:', error);
            return [];
        }
    }

    function getCurrentTagsFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const tagsParam = urlParams.get('tagSlug');
        if (!tagsParam) {
            return [];
        }
        return tagsParam.split(';').filter(tag => tag.trim() !== '');
    }

    function applyFilter() {
        if (isProgrammaticChange) {
            return;
        }

        const selectedValues = choices.getValue(true);
        const url = new URL(window.location.href);

        url.searchParams.delete('tagSlug');

        if (selectedValues && selectedValues.length > 0) {
            const tagsString = selectedValues.join(';');
            url.searchParams.set('tagSlug', tagsString);
            url.searchParams.set('page', '1');
        } else {
            url.searchParams.set('page', '1');
        }

        window.location.href = url.toString();
    }

    loadTags().then(tags => {
        choices.setChoices(tags, 'value', 'label', true);

        const selectedTags = getCurrentTagsFromUrl();
        if (selectedTags && selectedTags.length > 0) {
            isProgrammaticChange = true;
            choices.setChoiceByValue(selectedTags);
            setTimeout(() => {
                isProgrammaticChange = false;
            }, 100);
        }
    });

    let searchTimeout;
    choices.passedElement.element.addEventListener('search', function(event) {
        clearTimeout(searchTimeout);
        const searchQuery = event.detail.value;

        searchTimeout = setTimeout(async () => {
            const tags = await loadTags(searchQuery);
            const currentTags = getCurrentTagsFromUrl();

            isProgrammaticChange = true;
            choices.setChoices(tags, 'value', 'label', true);

            if (currentTags && currentTags.length > 0) {
                choices.setChoiceByValue(currentTags);
            }

            setTimeout(() => {
                isProgrammaticChange = false;
            }, 100);
        }, 300);
    });

    choices.passedElement.element.addEventListener('addItem', function() {
        if (isProgrammaticChange) {
            return;
        }

        if (filterTimeout) {
            clearTimeout(filterTimeout);
        }

        filterTimeout = setTimeout(() => {
            applyFilter();
        }, 1000);
    });

    choices.passedElement.element.addEventListener('removeItem', function() {
        if (isProgrammaticChange) {
            return;
        }

        if (filterTimeout) {
            clearTimeout(filterTimeout);
        }

        filterTimeout = setTimeout(() => {
            applyFilter();
        }, 1000);
    });
});

