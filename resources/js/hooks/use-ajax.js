/**
 * @typedef {Object} AjaxOptions
 * @property {string} url
 * @property {'GET'|'POST'|'DELETE'|'PATCH'} [method]
 * @property {Object} [data]
 */

export function useAjax() {
    const ajx = ({ url, method = 'GET', data = {} }) => {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/mia/'+ url,
                method,
                dataType: 'json',
                data,
                success: resolve,
                error: reject
            })
        })
    }

    return {
        post: (options) => ajx({ ...options, method: 'POST' }),
        get: (options) => ajx({ ...options, method: 'GET' }),
        del: (options) => ajx({ ...options, method: 'DELETE' }),
        patch: (options) => ajx({ ...options, method: 'PATCH' })
    }
}