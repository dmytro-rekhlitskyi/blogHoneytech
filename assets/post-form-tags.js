import Choices from 'choices.js';

document.addEventListener('DOMContentLoaded', function() {
    const tagSelectElement = document.getElementById('post_tags');

    if (!tagSelectElement) {
        return;
    }

    let isProgrammaticChange = false;

    function getInitialSelectedTags() {
        const selectedOptions = Array.from(tagSelectElement.selectedOptions);
        return selectedOptions.map(option => ({
            value: option.value,
            label: option.text,
        })).filter(item => item.value !== '');
    }

    const initialSelectedTags = getInitialSelectedTags();
    const initialSelectedTagIds = initialSelectedTags.map(tag => tag.value);

    tagSelectElement.innerHTML = '';

    const choices = new Choices(tagSelectElement, {
        removeItemButton: true,
        placeholder: true,
        placeholderValue: 'Select tags',
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
                value: tag.id.toString(),
                label: tag.name,
            }));
        } catch (error) {
            console.error('Error loading tags:', error);
            return [];
        }
    }

    loadTags().then(tags => {
        const loadedTagValues = new Set(tags.map(tag => tag.value));

        if (initialSelectedTags.length > 0) {
            const missingTags = initialSelectedTags.filter(item =>
                !loadedTagValues.has(item.value)
            );

            if (missingTags.length > 0) {
                tags = [...tags, ...missingTags];
            }
        }

        const uniqueTags = [];
        const seenValues = new Set();
        for (const tag of tags) {
            if (!seenValues.has(tag.value)) {
                seenValues.add(tag.value);
                uniqueTags.push(tag);
            }
        }

        isProgrammaticChange = true;
        choices.clearChoices();
        choices.setChoices(uniqueTags, 'value', 'label', false);

        if (initialSelectedTagIds && initialSelectedTagIds.length > 0) {
            choices.setChoiceByValue(initialSelectedTagIds);
        }

        setTimeout(() => {
            isProgrammaticChange = false;
        }, 100);
    });
});

