// Supabase Real-time Client Configuration
import { createClient } from '@supabase/supabase-js'

// Initialize Supabase client with environment variables
const supabaseUrl = import.meta.env.VITE_SUPABASE_URL
const supabaseAnonKey = import.meta.env.VITE_SUPABASE_ANON_KEY

console.log('Supabase URL:', supabaseUrl)
console.log('Supabase Key exists:', !!supabaseAnonKey)

if (!supabaseUrl || !supabaseAnonKey) {
    console.error('Missing Supabase environment variables')
    console.error('VITE_SUPABASE_URL:', supabaseUrl)
    console.error('VITE_SUPABASE_ANON_KEY exists:', !!supabaseAnonKey)
}

export const supabase = createClient(supabaseUrl, supabaseAnonKey, {
    realtime: {
        params: {
            eventsPerSecond: 10
        }
    },
    auth: {
        persistSession: false // We're using Laravel auth, not Supabase auth
    }
})

// Real-time subscription for attendance records
export function subscribeToAttendanceUpdates(userId, callback) {
    console.log('Setting up real-time subscription for user:', userId)
    
    const subscription = supabase
        .channel(`attendance_changes_${userId}`)
        .on(
            'postgres_changes',
            {
                event: '*',
                schema: 'public',
                table: 'attendance_records',
                filter: `user_id=eq.${userId}`
            },
            (payload) => {
                console.log('Real-time attendance update received:', payload)
                callback(payload)
            }
        )
        .subscribe((status) => {
            console.log('Subscription status:', status)
            if (status === 'SUBSCRIBED') {
                console.log('✅ Successfully subscribed to attendance updates')
            } else if (status === 'CHANNEL_ERROR') {
                console.error('❌ Failed to subscribe to attendance updates')
            }
        })

    return subscription
}

// Real-time subscription for all attendance records (for admin)
export function subscribeToAllAttendanceUpdates(callback) {
    console.log('Setting up real-time subscription for all attendance records')
    
    const subscription = supabase
        .channel('all_attendance_changes')
        .on(
            'postgres_changes',
            {
                event: '*',
                schema: 'public',
                table: 'attendance_records'
            },
            (payload) => {
                console.log('Real-time attendance update received:', payload)
                callback(payload)
            }
        )
        .subscribe((status) => {
            console.log('Subscription status:', status)
            if (status === 'SUBSCRIBED') {
                console.log('✅ Successfully subscribed to all attendance updates')
            } else if (status === 'CHANNEL_ERROR') {
                console.error('❌ Failed to subscribe to all attendance updates')
            }
        })

    return subscription
}

// Unsubscribe from real-time updates
export function unsubscribeFromUpdates(subscription) {
    if (subscription) {
        console.log('Unsubscribing from real-time updates')
        supabase.removeChannel(subscription)
    }
}

// Helper function to format time in PHT
export function formatTimePHT(timestamp) {
    const date = new Date(timestamp)
    return new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    }).format(date) + ' PHT'
}

// Helper function to format date in PHT
export function formatDatePHT(timestamp) {
    const date = new Date(timestamp)
    return new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(date)
}

// Helper function to format full date and time in PHT
export function formatDateTimePHT(timestamp) {
    const date = new Date(timestamp)
    return new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    }).format(date) + ' PHT'
}

// Helper function to get current PHT time
export function getCurrentPHTTime() {
    const now = new Date()
    return new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    }).format(now) + ' PHT'
}

// Helper function to calculate hours between two timestamps
export function calculateHours(timeIn, timeOut) {
    if (!timeIn || !timeOut) return null
    
    const start = new Date(timeIn)
    const end = new Date(timeOut)
    const diffMs = end - start
    const diffHours = diffMs / (1000 * 60 * 60)
    
    return Math.max(0, diffHours).toFixed(2)
}

// Test Supabase connection
export async function testSupabaseConnection() {
    try {
        console.log('Testing Supabase connection...')
        const { data, error } = await supabase
            .from('attendance_records')
            .select('count')
            .limit(1)
        
        if (error) {
            console.error('Supabase connection test failed:', error)
            return false
        }
        
        console.log('✅ Supabase connection successful')
        return true
    } catch (error) {
        console.error('Supabase connection test error:', error)
        return false
    }
}