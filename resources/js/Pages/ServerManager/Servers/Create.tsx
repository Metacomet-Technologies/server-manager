import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import Layout from '@/Components/ServerManager/Layout';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        host: '',
        port: 22,
        username: '',
        auth_type: 'password',
        password: '',
        private_key: '',
    });
    
    const [showPrivateKey, setShowPrivateKey] = useState(false);
    
    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('server-manager.servers.store'));
    };
    
    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                setData('private_key', e.target.result);
            };
            reader.readAsText(file);
        }
    };
    
    return (
        <Layout>
            <Head title="Add Server" />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="md:grid md:grid-cols-3 md:gap-6">
                        <div className="md:col-span-1">
                            <div className="px-4 sm:px-0">
                                <h3 className="text-lg font-medium leading-6 text-gray-900">
                                    Add Server
                                </h3>
                                <p className="mt-1 text-sm text-gray-600">
                                    Configure a new server connection.
                                </p>
                            </div>
                        </div>
                        
                        <div className="mt-5 md:mt-0 md:col-span-2">
                            <form onSubmit={handleSubmit}>
                                <div className="shadow sm:rounded-md sm:overflow-hidden">
                                    <div className="px-4 py-5 bg-white space-y-6 sm:p-6">
                                        <div>
                                            <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                                Server Name
                                            </label>
                                            <input
                                                type="text"
                                                name="name"
                                                id="name"
                                                value={data.name}
                                                onChange={e => setData('name', e.target.value)}
                                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                required
                                            />
                                            {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                                        </div>
                                        
                                        <div className="grid grid-cols-6 gap-6">
                                            <div className="col-span-4">
                                                <label htmlFor="host" className="block text-sm font-medium text-gray-700">
                                                    Host
                                                </label>
                                                <input
                                                    type="text"
                                                    name="host"
                                                    id="host"
                                                    value={data.host}
                                                    onChange={e => setData('host', e.target.value)}
                                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                    required
                                                />
                                                {errors.host && <p className="mt-1 text-sm text-red-600">{errors.host}</p>}
                                            </div>
                                            
                                            <div className="col-span-2">
                                                <label htmlFor="port" className="block text-sm font-medium text-gray-700">
                                                    Port
                                                </label>
                                                <input
                                                    type="number"
                                                    name="port"
                                                    id="port"
                                                    value={data.port}
                                                    onChange={e => setData('port', e.target.value)}
                                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                    required
                                                />
                                                {errors.port && <p className="mt-1 text-sm text-red-600">{errors.port}</p>}
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label htmlFor="username" className="block text-sm font-medium text-gray-700">
                                                Username
                                            </label>
                                            <input
                                                type="text"
                                                name="username"
                                                id="username"
                                                value={data.username}
                                                onChange={e => setData('username', e.target.value)}
                                                className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                required
                                            />
                                            {errors.username && <p className="mt-1 text-sm text-red-600">{errors.username}</p>}
                                        </div>
                                        
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">
                                                Authentication Type
                                            </label>
                                            <div className="mt-2 space-x-4">
                                                <label className="inline-flex items-center">
                                                    <input
                                                        type="radio"
                                                        name="auth_type"
                                                        value="password"
                                                        checked={data.auth_type === 'password'}
                                                        onChange={e => setData('auth_type', e.target.value)}
                                                        className="form-radio text-indigo-600"
                                                    />
                                                    <span className="ml-2">Password</span>
                                                </label>
                                                <label className="inline-flex items-center">
                                                    <input
                                                        type="radio"
                                                        name="auth_type"
                                                        value="key"
                                                        checked={data.auth_type === 'key'}
                                                        onChange={e => setData('auth_type', e.target.value)}
                                                        className="form-radio text-indigo-600"
                                                    />
                                                    <span className="ml-2">SSH Key</span>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        {data.auth_type === 'password' ? (
                                            <div>
                                                <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                                                    Password
                                                </label>
                                                <input
                                                    type="password"
                                                    name="password"
                                                    id="password"
                                                    value={data.password}
                                                    onChange={e => setData('password', e.target.value)}
                                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                    required
                                                />
                                                {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                                            </div>
                                        ) : (
                                            <div>
                                                <label htmlFor="private_key" className="block text-sm font-medium text-gray-700">
                                                    Private Key
                                                </label>
                                                <div className="mt-1 flex items-center">
                                                    <input
                                                        type="file"
                                                        name="private_key_file"
                                                        id="private_key_file"
                                                        onChange={handleFileChange}
                                                        className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                                        accept=".pem,.key,.ppk,id_rsa,id_ed25519"
                                                    />
                                                </div>
                                                <p className="mt-1 text-sm text-gray-500">Or paste the key directly:</p>
                                                <textarea
                                                    name="private_key"
                                                    id="private_key"
                                                    rows={showPrivateKey ? 10 : 3}
                                                    value={data.private_key}
                                                    onChange={e => setData('private_key', e.target.value)}
                                                    onFocus={() => setShowPrivateKey(true)}
                                                    onBlur={() => setShowPrivateKey(false)}
                                                    className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md font-mono text-xs"
                                                    placeholder="-----BEGIN RSA PRIVATE KEY-----"
                                                    required
                                                />
                                                {errors.private_key && <p className="mt-1 text-sm text-red-600">{errors.private_key}</p>}
                                            </div>
                                        )}
                                    </div>
                                    
                                    <div className="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                        <Link
                                            href={route('server-manager.servers.index')}
                                            className="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3"
                                        >
                                            Cancel
                                        </Link>
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            {processing ? 'Saving...' : 'Save'}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
}