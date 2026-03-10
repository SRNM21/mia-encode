/**
 * @typedef {Object} AjaxOptions
 * @property {string} url
 * @property {'GET'|'POST'|'DELETE'|'PATCH'} [method]
 * @property {Object} [data]
 * @property {(response: any) => void} [success]
 * @property {(xhr: JQuery.jqXHR) => void} [error]
 */

export function useAjax() {
    /**
     * @param {AjaxOptions} options
     */
    const ajx = async ({ url, method = 'GET', data = {}, success, error }) => {
        $.ajax({
            url,
            method,
            dataType: 'json',
            data,
            success,
            error
        })
    }

    return {
        /** @param {AjaxOptions} options */
        post: (options) => ajx({ ...options, method: 'POST' }),
        
        /** @param {AjaxOptions} options */
        get: (options) => ajx({ ...options, method: 'GET' }),

        /** @param {AjaxOptions} options */
        del: (options) => ajx({ ...options, method: 'DELETE' }),

        /** @param {AjaxOptions} options */
        patch: (options) => ajx({ ...options, method: 'PATCH' })
    }
}