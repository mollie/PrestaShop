"use client"

const SkeletonInfoBox = () => (
  <div className="info-box animate-pulse">
    <div className="h-5 w-5 bg-gray-200 rounded-full flex-shrink-0"></div>
    <div className="info-content flex-1">
      <div className="h-4 bg-gray-200 rounded w-full mb-2"></div>
      <div className="h-4 bg-gray-200 rounded w-5/6 mb-2"></div>
      <div className="h-4 bg-gray-200 rounded w-4/6"></div>
    </div>
  </div>
)

const SkeletonToggleGroup = () => (
  <div className="toggle-group animate-pulse">
    <div className="toggle-content">
      <div className="flex-1">
        <div className="h-5 bg-gray-200 rounded w-48 mb-2"></div>
        <div className="h-3 bg-gray-200 rounded w-64"></div>
      </div>
      <div className="h-6 w-11 bg-gray-200 rounded-full"></div>
      <div className="h-4 bg-gray-200 rounded w-16"></div>
    </div>
  </div>
)

const SkeletonCarrierTable = () => (
  <div className="carrier-table animate-pulse">
    {Array.from({ length: 3 }).map((_, index) => (
      <div key={index} className="carrier-row">
        <div className="carrier-col carrier-name">
          <div className="h-4 bg-gray-200 rounded w-32"></div>
        </div>
        <div className="carrier-col carrier-select">
          <div className="h-10 bg-gray-200 rounded w-full"></div>
        </div>
        <div className="carrier-col carrier-input">
          <div className="h-10 bg-gray-200 rounded w-full"></div>
        </div>
      </div>
    ))}
  </div>
)

const SkeletonStatusMapping = () => (
  <div className="status-mapping-table animate-pulse">
    {Array.from({ length: 4 }).map((_, index) => (
      <div key={index} className="status-mapping-row">
        <div className="status-mapping-col">
          <div className="h-4 bg-gray-200 rounded w-24 mb-1"></div>
          <div className="h-3 bg-gray-200 rounded w-32"></div>
        </div>
        <div className="status-mapping-col">
          <div className="h-3 bg-gray-200 rounded w-32 ml-auto"></div>
        </div>
        <div className="status-mapping-col">
          <div className="h-10 bg-gray-200 rounded w-full"></div>
        </div>
      </div>
    ))}
  </div>
)

const SkeletonEmailStatus = () => (
  <div className="email-status-list animate-pulse">
    {Array.from({ length: 3 }).map((_, index) => (
      <div key={index} className="email-status-row">
        <div className="email-status-info">
          <div className="h-4 bg-gray-200 rounded w-32 mb-1"></div>
          <div className="h-3 bg-gray-200 rounded w-40"></div>
        </div>
        <div className="email-status-toggle">
          <div className="h-6 w-11 bg-gray-200 rounded-full"></div>
          <div className="h-4 bg-gray-200 rounded w-32"></div>
        </div>
      </div>
    ))}
  </div>
)

export function AdvancedSettingsSkeleton() {
  return (
    <div className="advanced-settings">
      <div className="settings-header">
        <div className="h-8 bg-gray-200 rounded w-64 mb-2 animate-pulse"></div>
        <div className="h-4 bg-gray-200 rounded w-96 animate-pulse"></div>
      </div>

      <section className="settings-section animate-pulse">
        <div className="h-6 bg-gray-200 rounded w-40 mb-6"></div>

        <div className="form-group mb-4">
          <div className="h-4 bg-gray-200 rounded w-72 mb-2"></div>
          <div className="h-10 bg-gray-200 rounded w-full"></div>
        </div>

        <SkeletonInfoBox />

        <div className="form-group mt-6">
          <div className="h-4 bg-gray-200 rounded w-64 mb-2"></div>
          <div className="h-10 bg-gray-200 rounded w-full"></div>
        </div>
      </section>

      <section className="settings-section">
        <div className="h-6 bg-gray-200 rounded w-48 mb-6 animate-pulse"></div>

        <SkeletonToggleGroup />

        <div className="form-group mb-4 animate-pulse">
          <div className="h-4 bg-gray-200 rounded w-80 mb-2"></div>
          <div className="h-10 bg-gray-200 rounded w-full"></div>
        </div>

        <div className="form-group mb-4 animate-pulse">
          <div className="h-4 bg-gray-200 rounded w-64 mb-2"></div>
        </div>

        <SkeletonInfoBox />

        <SkeletonCarrierTable />
      </section>

      <section className="settings-section">
        <div className="h-6 bg-gray-200 rounded w-44 mb-6 animate-pulse"></div>

        <div className="form-group mb-4 animate-pulse">
          <div className="h-4 bg-gray-200 rounded w-64 mb-2"></div>
          <div className="button-group mb-4">
            {Array.from({ length: 3 }).map((_, index) => (
              <div key={index} className="h-10 bg-gray-200 rounded flex-1"></div>
            ))}
          </div>
          <div className="h-12 bg-gray-200 rounded w-full"></div>
        </div>

        <div className="form-group mb-4 animate-pulse">
          <div className="h-4 bg-gray-200 rounded w-48 mb-2"></div>
          <div className="h-10 bg-gray-200 rounded w-full"></div>
        </div>

        <div className="form-group animate-pulse">
          <div className="h-4 bg-gray-200 rounded w-56 mb-2"></div>
          <div className="h-10 bg-gray-200 rounded w-full"></div>
        </div>
      </section>

      <section className="settings-section">
        <div className="h-6 bg-gray-200 rounded w-52 mb-6 animate-pulse"></div>

        <SkeletonInfoBox />

        <SkeletonStatusMapping />
      </section>

      <section className="settings-section">
        <div className="h-6 bg-gray-200 rounded w-48 mb-6 animate-pulse"></div>

        <SkeletonInfoBox />

        <SkeletonEmailStatus />
      </section>

      <div className="settings-footer">
        <div className="h-11 bg-gray-200 rounded w-40 animate-pulse"></div>
      </div>
    </div>
  )
}
