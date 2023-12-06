import { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { useDispatch } from '@wordpress/data';
import { TextControl, Button, PanelBody, PanelRow, Panel } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

function SettingsPage() {
    const { createNotice } = useDispatch('core/notices');
    const [settings, setSettings] = useState({ repo: '', token: '', remoteDir: '' });

    useEffect(() => {
        apiFetch('/wp-json/gitenberg/v1/settings/')
            .then(response => response.json())
            .then(data => setSettings(data))
            .catch(error => createNotice('error', 'Error fetching settings: ' + error.message));
    }, []);

    const saveSettings = () => {
        apiFetch('/wp-json/gitenberg/v1/settings/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': gitenbergSettings.nonce
            },
            body: JSON.stringify(settings)
        })
        .then(response => response.json())
        .then(data => {
            createNotice('success', data.message);
        })
        .catch(error => {
            createNotice('error', 'Error updating settings: ' + error.message);
        });
    };

    return (
        <div style={{maxWidth: '300px'}} >
            <Panel header="Gitenberg Settings">
                <React.Fragment key=".0">
                    <PanelBody title="GitHub">
                        <PanelRow>
                            <TextControl
                                label="Repository"
                                value={settings.repo}
                                onChange={(repo) => setSettings({ ...settings, repo })}
                            />
                        </PanelRow>
                        <PanelRow>
                            <TextControl
                                label="Personal Access Token"
                                value={settings.token}
                                onChange={(token) => setSettings({ ...settings, token })}
                            />
                        </PanelRow>
                        <PanelRow>
                            <TextControl
                                label="Remote Directory"
                                value={settings.remoteDir}
                                onChange={(remoteDir) => setSettings({ ...settings, remoteDir })}
                            />
                        </PanelRow>
                    </PanelBody>
                    <PanelBody>
                        <PanelRow>
                            <Button variant='primary' onClick={saveSettings}>Save</Button>
                        </PanelRow>
                    </PanelBody>
                </React.Fragment>
            </Panel>
        </div>
    );
}

const container = document.getElementById('gitenberg-settings');
const root = createRoot(container);
root.render(<SettingsPage />);