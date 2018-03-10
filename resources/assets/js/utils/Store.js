/**
 * json:api compliant Store
 */
export default {
    store: {},
    /**
     * Get an item from the store (internal method)
     * @param store
     * @param type
     * @param id
     * @returns {*}
     */
    getFromStore(store, type, id) {
        // Skip tests if null
        if (id === null) {
            return null;
        }

        if (store[type] && store[type][id]) {
            return store[type][id];
        }

        return null;
    },
    /**
     * Get all items of a given type from the store
     * @param type
     * @returns {Array}
     */
    all(type) {
        if (this.store[type]) {
            return Object.keys(this.store[type]).map(key => this.store[type][key]);
        }

        return [];
    },
    /**
     * Get item from the store
     * @param param1
     * @param param2
     * @returns {*}
     */
    get(param1, param2) {
        // Don't error on null, allows to pass null relationships
        if (param1 === null) {
            return null;
        }

        let type = null;
        let id = null;

        if (typeof param2 === 'undefined') {
            // If a full relationship object was passed, we use the data as the description
            // Will only work for non-collection relations
            if (typeof param1.data !== 'undefined') {
                param1 = param1.data;
            }

            type = param1.type;
            id = param1.id;
        } else {
            type = param1;
            id = param2;
        }

        return this.getFromStore(this.store, type, id);
    },
    /**
     * Load data into the store (single item)
     * @param data
     */
    setInStore(data) {
        if (!this.store[data.type]) {
            this.store[data.type] = {};
        }

        if (this.store[data.type][data.id]) {
            // Replace all attributes with the new ones
            this.store[data.type][data.id].attributes = data.attributes;

            // If the object is already in store, only replace new relationships
            for (let relation in data.relationships) {
                if (data.relationships.hasOwnProperty(relation)) {
                    // Create relationship if not previously existing
                    if (!this.store[data.type][data.id].relationships) {
                        this.store[data.type][data.id].relationships = {};
                    }

                    this.store[data.type][data.id].relationships[relation] = data.relationships[relation];
                }
            }
        } else {
            this.store[data.type][data.id] = data;
        }
    },
    /**
     * Load data into the store (single item or list)
     * @param included
     */
    load(included) {
        if (Array.isArray(included)) {
            included.forEach(item => this.setInStore(item));
        } else {
            this.setInStore(included);
        }
    }
}
