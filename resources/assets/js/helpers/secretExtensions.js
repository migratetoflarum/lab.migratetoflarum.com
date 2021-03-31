// Some fun fake extensions
import App from "../utils/App";

const EXTENSIONS = [
    {
        package: 'backdoor',
        title: 'Top Secret Backdoor',
        description: 'Completely free extension with lots of hidden features.',
        icon: {
            name: 'fas fa-door-open',
            color: '#aaaaaa',
            backgroundColor: '#000000',
        },
    },
    {
        package: 'candy',
        title: 'Candy Distributor',
        description: 'Sends candies to new users by post, worldwide. Forum owner is automatically billed.',
        icon: {
            name: 'fas fa-candy-cane',
            color: '#ffffff',
            backgroundColor: '#e7672e',
        },
    },
    {
        package: 'no-ios',
        title: 'No iOS',
        description: 'Prevents access to the forum to iPhones on iOS 9, iPads on iOS 13, as well as Mac OS with iMovie installed.',
        icon: {
            name: 'fab fa-apple',
            color: '#888888',
            backgroundColor: '#dddddd',
        },
    },
    {
        package: 'particle-accelerator',
        title: 'Particle Accelerator',
        description: 'Improves forum performance by accelerating electrons on your server hardware.',
        icon: {
            name: 'fas fa-bolt',
            color: '#ffea7b',
            backgroundColor: '#3d3d3d',
        },
    },
    {
        package: 'no-smoking',
        title: 'No Smoking',
        description: 'Adds no smoking signs on the forum.',
        icon: {
            name: 'fas fa-smoking-ban',
            color: '#333333',
            backgroundColor: '#6dbb3e',
        },
    },
    {
        package: 'meme-only',
        title: 'Meme Only',
        description: 'Users can only reply with viral meme images. Normal comments are disabled.',
        icon: {
            name: 'fas fa-camera-retro',
            color: '#a4226c',
            backgroundColor: '#ffffff',
        },
    },
    {
        package: 'advanced-search',
        title: 'Advanced user search',
        description: 'Adds ability to search users by their mother\'s maiden name, dog name, birthday and password.',
        icon: {
            name: 'fas fa-search-dollar',
            color: '#ffffff',
            backgroundColor: '#1955eb',
        },
    },
    {
        package: 'cloud',
        title: 'Cloud Database',
        description: 'Exports a copy of your data to a floppy disk and sends it in the clouds in a hot balloon.',
        icon: {
            name: 'fas fa-cloud',
            color: '#ffffff',
            backgroundColor: '#4b93d1',
        },
    },
    {
        package: 'free-wifi',
        title: 'Free wifi',
        description: 'Turns every visitor laptop and mobile phone into a free wifi access point.',
        icon: {
            name: 'fas fa-wifi',
            color: '#65baf6',
            backgroundColor: '#dddddd',
        },
    },
    {
        package: 'flarum-ban',
        title: 'Ban Flarum Team',
        description: 'Hides posts with 140+ characters from the Flarum core team.',
        icon: {
            name: 'fas fa-eye-slash',
            color: '#ffffff',
            backgroundColor: '#dd541e',
        },
    },
];

// Store the random values by scan, so it doesn't change when redrawing the page
let randomStore = {};

export default function (scan, extensions) {
    // Do not add extensions if none exist on the forum
    if (extensions.length === 0) {
        return;
    }

    if (!randomStore.hasOwnProperty(scan.id)) {
        randomStore[scan.id] = {
            chance: Math.random() * 100,
            extensionIndex: Math.floor(Math.random() * EXTENSIONS.length),
            version: Math.floor(Math.random() * 3) + '.' + (Math.floor(Math.random() * 10) + 1) + '.' + Math.floor(Math.random() * 5),
            placement: Math.random(),
        };
    }

    const random = randomStore[scan.id];

    // Add random extension based on probability
    if (random.chance >= App.secretExtensionProbability) {
        return;
    }

    const extension = EXTENSIONS[random.extensionIndex];

    const repoLink = 'https://flarum.dev/repo/migratetoflarum/flarum-ext-' + extension.package;

    let insertAtIndex = 0;

    if (extensions.length > 0) {
        // Limit to placement in the first 3
        const maximumIndexForInsert = Math.min(3, extensions.length);

        insertAtIndex = Math.floor(random.placement * maximumIndexForInsert);
    }

    extensions.splice(insertAtIndex, 0, {
        attributes: {
            package: 'migratetoflarum/flarum-ext-' + extension.package,
            title: extension.title,
            description: extension.description,
            icon: extension.icon,
            repository: repoLink,
            customPackagistLink: repoLink,
            customExtiverseLink: repoLink,
            possible_versions: [
                random.version,
            ],
        },
    });
}
