import React from 'react'

interface SimplePSAccountsProps {
  psAccountsCdnUrl?: string
  cloudSyncCdnUrl?: string
  onOnboardingCompleted?: (isCompleted: boolean) => void
}

export default function SimplePSAccounts({
  psAccountsCdnUrl,
  cloudSyncCdnUrl,
  onOnboardingCompleted
}: SimplePSAccountsProps) {
  console.log('🚀 SimplePSAccounts rendered!', { psAccountsCdnUrl, cloudSyncCdnUrl })

  return (
    <div style={{
      marginBottom: '2rem',
      border: '3px solid #007cba',
      borderRadius: '8px',
      padding: '2rem',
      backgroundColor: '#e8f4f8',
      textAlign: 'center'
    }}>
      <h2 style={{ 
        margin: '0 0 1rem 0', 
        color: '#007cba',
        fontSize: '1.5rem',
        fontWeight: 'bold'
      }}>
        🎉 PS Accounts Integration Active!
      </h2>
      
      <div style={{
        backgroundColor: 'white',
        padding: '1rem',
        borderRadius: '4px',
        marginBottom: '1rem',
        textAlign: 'left'
      }}>
        <h3 style={{ margin: '0 0 0.5rem 0', fontSize: '1.1rem' }}>Configuration Status:</h3>
        <p style={{ margin: '0.25rem 0', fontSize: '0.9rem' }}>
          <strong>PS Accounts CDN URL:</strong> {psAccountsCdnUrl || '❌ Not provided'}
        </p>
        <p style={{ margin: '0.25rem 0', fontSize: '0.9rem' }}>
          <strong>CloudSync CDN URL:</strong> {cloudSyncCdnUrl || '❌ Not provided'}
        </p>
      </div>

      <p style={{ 
        margin: 0, 
        fontSize: '1rem', 
        color: '#333'
      }}>
        If you can see this, the React integration is working! 🎯
      </p>
      
      {!psAccountsCdnUrl && !cloudSyncCdnUrl && (
        <div style={{
          marginTop: '1rem',
          padding: '1rem',
          backgroundColor: '#fff3cd',
          border: '1px solid #ffeaa7',
          borderRadius: '4px'
        }}>
          <p style={{ margin: 0, fontWeight: 'bold' }}>
            ⚠️ PS Accounts modules not detected or API endpoint not working
          </p>
        </div>
      )}
    </div>
  )
}